<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProformaController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers,
        private StockService $stockService,
        private AccountingService $accounting,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Invoice::where('type', 'proforma');
        $this->branchContext->applyScope($q);

        if ($request->status) {
            $q->where('status', $request->status);
        }

        $rows = $q->with(['customer', 'branch', 'createdBy'])
            ->latest('invoice_date')
            ->get()
            ->map(fn($i) => $this->transform($i));

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'customer_id'      => 'required|exists:customers,id',
            'proforma_date'    => 'required|date',
            'valid_until'      => 'nullable|date',
            'status'           => 'required|in:draft,sent',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount'  => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
            'reference'        => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.discount_percent'=> 'nullable|numeric|min:0|max:100',
            'items.*.notes'           => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $subtotal = collect($data['items'])->sum(
                fn($i) => $i['unit_price'] * $i['quantity'] * (1 - (($i['discount_percent'] ?? 0) / 100))
            );
            $discountAmt = ($data['discount_amount'] ?? 0) > 0
                ? (float) $data['discount_amount']
                : $subtotal * (($data['discount_percent'] ?? 0) / 100);
            $total = round($subtotal - $discountAmt, 2);

            $invoice = Invoice::create([
                'branch_id'        => $data['branch_id'],
                'customer_id'      => $data['customer_id'],
                'created_by'       => $request->user()->id,
                'invoice_number'   => $this->numbers->invoiceNumber($data['branch_id'], 'proforma'),
                'type'             => 'proforma',
                'status'           => $data['status'],
                'invoice_date'     => $data['proforma_date'],
                'due_date'         => $data['valid_until'] ?? null,
                'subtotal'         => round($subtotal, 2),
                'discount_percent' => $data['discount_percent'] ?? 0,
                'discount_amount'  => round($discountAmt, 2),
                'tax_percent'      => 0,
                'tax_amount'       => 0,
                'total'            => $total,
                'paid_amount'      => 0,
                'balance_due'      => $total,
                'notes'            => $data['notes'] ?? null,
                'terms'            => $data['reference'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                $lineDiscount = $item['unit_price'] * $item['quantity'] * (($item['discount_percent'] ?? 0) / 100);
                $lineTotal    = ($item['unit_price'] * $item['quantity']) - $lineDiscount;

                InvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'product_id'       => $item['product_id'],
                    'product_name'     => $product->name,
                    'product_code'     => $product->code ?? '',
                    'unit'             => $product->unit ?? 'pcs',
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_amount'  => round($lineDiscount, 2),
                    'tax_percent'      => 0,
                    'tax_amount'       => 0,
                    'total'            => round($lineTotal, 2),
                    'notes'            => $item['notes'] ?? null,
                ]);
            }

            return response()->json(
                $this->transform($invoice->load(['items.product', 'customer', 'branch', 'createdBy'])),
                201
            );
        }, 5);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        if ($invoice->type !== 'proforma') {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(
            $this->transform($invoice->load(['items.product', 'customer', 'branch', 'createdBy']))
        );
    }

    public function convert(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->type !== 'proforma') {
            return response()->json(['message' => 'Not a proforma invoice.'], 422);
        }
        if ($invoice->status === 'converted') {
            return response()->json(['message' => 'Already converted.', 'invoice_id' => $invoice->original_invoice_id], 422);
        }
        if ($invoice->status === 'cancelled') {
            return response()->json(['message' => 'Cannot convert a cancelled proforma.'], 422);
        }

        $data = $request->validate([
            'invoice_date'        => 'nullable|date',
            'due_date'            => 'nullable|date',
            'payment_terms_days'  => 'nullable|integer|min:0',
            'discount_percent'    => 'nullable|numeric|min:0|max:100',
            'discount_amount'     => 'nullable|numeric|min:0',
            'tax_percent'         => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string',
            'items'               => 'nullable|array|min:1',
            'items.*.product_id'       => 'required_with:items|exists:products,id',
            'items.*.quantity'         => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price'       => 'required_with:items|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.notes'            => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($invoice, $request, $data) {
            $invoice->loadMissing('items');

            // Use submitted items or fall back to proforma items
            $itemsSource = !empty($data['items']) ? $data['items'] : $invoice->items->map(fn($it) => [
                'product_id'       => $it->product_id,
                'quantity'         => $it->quantity,
                'unit_price'       => $it->unit_price,
                'discount_percent' => $it->discount_percent,
                'notes'            => $it->notes,
            ])->toArray();

            $taxPct      = (float) ($data['tax_percent'] ?? $invoice->tax_percent ?? 0);
            $discPct     = (float) ($data['discount_percent'] ?? $invoice->discount_percent ?? 0);
            $discAmt     = (float) ($data['discount_amount'] ?? 0);

            $subtotal = collect($itemsSource)->sum(
                fn($i) => $i['unit_price'] * $i['quantity'] * (1 - (($i['discount_percent'] ?? 0) / 100))
            );
            $discount   = $discAmt > 0 ? $discAmt : round($subtotal * $discPct / 100, 2);
            $taxable    = $subtotal - $discount;
            $taxAmount  = round($taxable * $taxPct / 100, 2);
            $total      = round($taxable + $taxAmount, 2);

            $invoiceDate = $data['invoice_date'] ?? $invoice->invoice_date->toDateString();

            $realInvoice = Invoice::create([
                'branch_id'           => $invoice->branch_id,
                'customer_id'         => $invoice->customer_id,
                'created_by'          => $invoice->created_by,
                'approved_by'         => $request->user()->id,
                'original_invoice_id' => $invoice->id,
                'invoice_number'      => $this->numbers->invoiceNumber($invoice->branch_id, 'invoice'),
                'type'                => 'invoice',
                'status'              => 'confirmed',
                'invoice_date'        => $invoiceDate,
                'due_date'            => $data['due_date'] ?? $invoice->due_date,
                'payment_terms_days'  => $data['payment_terms_days'] ?? $invoice->payment_terms_days ?? 30,
                'subtotal'            => round($subtotal, 2),
                'discount_percent'    => $discPct,
                'discount_amount'     => $discount,
                'tax_percent'         => $taxPct,
                'tax_amount'          => $taxAmount,
                'total'               => $total,
                'paid_amount'         => 0,
                'balance_due'         => $total,
                'notes'               => $data['notes'] ?? $invoice->notes,
                'terms'               => $invoice->terms,
                'approved_at'         => now(),
            ]);

            foreach ($itemsSource as $item) {
                $product      = Product::find($item['product_id']);
                $lineDiscount = $item['unit_price'] * $item['quantity'] * (($item['discount_percent'] ?? 0) / 100);
                $lineSubtotal = ($item['unit_price'] * $item['quantity']) - $lineDiscount;
                $lineTax      = round($lineSubtotal * $taxPct / 100, 2);
                $batch = \App\Models\Batch::find($item['batch_id'] ?? null)
                    ?? $this->stockService->pickOldestBatch($item['product_id'], $invoice->branch_id)
                    ?? $this->stockService->getOrCreateBackorderBatch($item['product_id'], $invoice->branch_id, $invoiceDate);

                InvoiceItem::create([
                    'invoice_id'       => $realInvoice->id,
                    'product_id'       => $item['product_id'],
                    'batch_id'         => $batch->id,
                    'product_name'     => $product->name,
                    'product_code'     => $product->code ?? '',
                    'unit'             => $product->unit ?? 'pcs',
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'unit_cost'        => (float) $batch->cost_price,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_amount'  => round($lineDiscount, 2),
                    'tax_percent'      => $taxPct,
                    'tax_amount'       => $lineTax,
                    'total'            => round($lineSubtotal + $lineTax, 2),
                    'batch_number'     => $batch->batch_number,
                    'notes'            => $item['notes'] ?? null,
                ]);

                $this->stockService->deductFromBatch(
                    $batch->id, $item['quantity'],
                    'sale_out', 'invoice', $realInvoice->id,
                    $request->user()->id, $invoiceDate
                );
            }

            $invoice->update(['status' => 'converted', 'original_invoice_id' => $realInvoice->id]);

            $this->accounting->postSalesJournal($realInvoice, $request->user()->id);

            return response()->json([
                'message'        => 'Proforma converted to invoice successfully.',
                'invoice_id'     => $realInvoice->id,
                'invoice_number' => $realInvoice->invoice_number,
            ]);
        }, 5);
    }

    private function transform(Invoice $i): array
    {
        return [
            'id'              => $i->id,
            'proforma_number' => $i->invoice_number,
            'proforma_date'   => $i->invoice_date,
            'valid_until'     => $i->due_date,
            'status'          => $i->status,
            'reference'       => $i->terms,
            'notes'           => $i->notes,
            'subtotal'        => $i->subtotal,
            'discount_amount' => $i->discount_amount,
            'total_amount'    => $i->total,
            'invoice_id'      => $i->status === 'converted' ? $i->original_invoice_id : null,
            'customer'        => $i->customer ? [
                'id'    => $i->customer->id,
                'name'  => $i->customer->name,
                'phone' => $i->customer->phone ?? '',
            ] : null,
            'branch'     => $i->branch     ? ['id' => $i->branch->id,     'name' => $i->branch->name]     : null,
            'created_by' => $i->createdBy  ? ['id' => $i->createdBy->id,  'name' => $i->createdBy->name]  : null,
            'items'      => $i->relationLoaded('items')
                ? $i->items->map(fn($it) => [
                    'id'           => $it->id,
                    'product_id'   => $it->product_id,
                    'product_name' => $it->product_name,
                    'product'      => $it->product ? ['id' => $it->product->id, 'name' => $it->product->name, 'sku' => $it->product->code] : null,
                    'qty'          => $it->quantity,
                    'quantity'     => $it->quantity,
                    'unit_price'   => $it->unit_price,
                    'discount'     => $it->discount_percent,
                    'total'        => $it->total,
                    'line_total'   => $it->total,
                ])->values()
                : [],
            'created_at' => $i->created_at,
        ];
    }
}
