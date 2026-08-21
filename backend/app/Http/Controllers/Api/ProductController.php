<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(
        private NumberGeneratorService $numbers,
        private StockService $stockService,
        private BranchContextService $branchContext
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Product::query();
        $this->branchContext->applyScope($q);
        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
                ->orWhere('sku', 'like', "%{$request->search}%")
                ->orWhere('brand', 'like', "%{$request->search}%")
            );
        }
        if ($request->category_id) $q->where('category_id', $request->category_id);
        if ($request->is_active !== null) $q->where('is_active', $request->boolean('is_active'));

        $branchId = $this->branchContext->getBranchId();
        $products = $q->with(['category', 'branchStocks' => fn($q) => ($branchId ? $q->where('branch_id', $branchId) : $q)->with('branch')])
            ->orderBy('name')
            ->paginate($request->input('per_page', 200));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sku' => 'nullable|string|unique:products',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'opening_stock' => 'nullable|numeric|min:0',
            'valuation_method' => 'nullable|in:fifo,weighted_average',
            'track_serial' => 'nullable|boolean',
            'track_batch' => 'nullable|boolean',
            'has_expiry' => 'nullable|boolean',
        ]);

        $openingStock = (float) ($data['opening_stock'] ?? 0);
        unset($data['opening_stock']);

        return DB::transaction(function () use ($data, $openingStock) {
            $data['code'] = $this->numbers->productCode();
            $product = Product::create($data);

            if ($openingStock > 0) {
                $branchId = $this->branchContext->getBranchId()
                    ?? \App\Models\Branch::orderBy('id')->value('id');
                $unitCost = (float) ($data['cost_price'] ?? 0);
                $sellingPrice = (float) ($data['selling_price'] ?? 0);

                $this->stockService->receiveBatch(
                    $product->id, $branchId, $openingStock, $unitCost, $sellingPrice,
                    'opening', 'product', $product->id, auth()->id(), now()->toDateString(),
                    null, null, 'opening'
                );
            }

            return response()->json($product->load('category'), 201);
        }, 5);
    }

    public function show(Product $product): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $product->load(['category', 'branchStocks' => fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q]);
        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'unit' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'valuation_method' => 'nullable|in:fifo,weighted_average',
            'track_serial' => 'nullable|boolean',
            'track_batch' => 'nullable|boolean',
            'has_expiry' => 'nullable|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($data);
        return response()->json($product->fresh(['category']));
    }

    public function destroy(Product $product): JsonResponse
    {
        $blockers = [
            'invoiceItems'          => 'sales invoices',
            'purchaseOrderItems'    => 'purchase orders',
            'grnItems'              => 'goods receipt notes',
            'purchaseReturnItems'   => 'purchase returns',
            'transferItems'         => 'branch transfers',
            'stockAdjustmentItems'  => 'stock adjustments',
            'batches'               => 'stock batches',
        ];

        foreach ($blockers as $relation => $label) {
            if ($product->{$relation}()->exists()) {
                return response()->json([
                    'message' => "Cannot delete a product that has {$label} on record. Deactivate it instead if it's no longer sold.",
                ], 422);
            }
        }

        $product->branchStocks()->delete();
        $product->delete();
        return response()->json(['message' => 'Product deleted.']);
    }

    public function stock(Request $request, Product $product): JsonResponse
    {
        $stock = $product->branchStocks()->with('branch')->get();
        return response()->json(['product' => $product, 'stock' => $stock]);
    }

    public function movements(Request $request, Product $product): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $q = $product->movements()->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        if ($request->from_date) $q->whereDate('movement_date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('movement_date', '<=', $request->to_date);
        return response()->json($q->with('branch')->latest('movement_date')->paginate(50));
    }

    public function batches(Request $request, Product $product): JsonResponse
    {
        $branchId = $request->branch_id ?? $this->branchContext->getBranchId();

        $batches = \App\Models\Batch::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('received_date')->orderBy('id')
            ->get()
            ->map(fn($b) => [
                'id'            => $b->id,
                'batch_code'    => $b->batch_code,
                'batch_number'  => $b->batch_number,
                'received_date' => $b->received_date,
                'expiry_date'   => $b->expiry_date,
                'available_qty' => (float) $b->quantity_remaining,
                'unit_cost'     => (float) $b->cost_price,
                'selling_price' => (float) $b->selling_price,
            ]);

        return response()->json($batches->values());
    }

    public function updateBatch(Request $request, \App\Models\Batch $batch): JsonResponse
    {
        $data = $request->validate([
            'cost_price'    => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
        ]);
        if (empty($data)) {
            return response()->json(['message' => 'Provide cost_price and/or selling_price.'], 422);
        }

        // Only the batch's own price changes — historical stock_movements,
        // invoice_items, and posted journal entries are never touched. This
        // affects only future sales drawing from this batch.
        $batch->update($data);
        $this->stockService->recomputeRollup($batch->product_id, $batch->branch_id);

        return response()->json($batch->fresh());
    }

    public function lowStock(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();

        if ($branchId) {
            $products = $this->stockService->getLowStockProducts($branchId);
            $data = $products->map(fn($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'code'          => $p->code,
                'sku'           => $p->sku,
                'category'      => $p->category,
                'reorder_level' => (float) $p->reorder_level,
                'stock_qty'     => (float) ($p->branchStocks->first()?->quantity ?? 0),
                'unit'          => $p->unit,
                'branch_name'   => null,
            ]);
            return response()->json($data);
        }

        // All branches — one row per product+branch combination
        $data = \App\Models\ProductBranchStock::query()
            ->join('products', 'product_branch_stock.product_id', '=', 'products.id')
            ->join('branches', 'product_branch_stock.branch_id', '=', 'branches.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->whereRaw('product_branch_stock.quantity <= products.reorder_level')
            ->where('products.is_active', true)
            ->orderBy('product_branch_stock.quantity')
            ->select([
                'products.id', 'products.name', 'products.code', 'products.sku',
                'products.reorder_level', 'products.unit',
                'product_branch_stock.quantity as stock_qty',
                'product_categories.name as category_name',
                'branches.name as branch_name',
            ])
            ->get()
            ->map(fn($r) => [
                'id'            => $r->id,
                'name'          => $r->name,
                'code'          => $r->code,
                'sku'           => $r->sku,
                'category'      => $r->category_name ? ['name' => $r->category_name] : null,
                'reorder_level' => (float) $r->reorder_level,
                'stock_qty'     => (float) $r->stock_qty,
                'unit'          => $r->unit,
                'branch_name'   => $r->branch_name,
            ]);

        return response()->json($data);
    }

    // Product Categories
    public function categories(Request $request): JsonResponse
    {
        $q = ProductCategory::with('children')->whereNull('parent_id');
        $this->branchContext->applyScope($q);
        return response()->json($q->get());
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:product_categories',
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
        ]);

        return response()->json(ProductCategory::create($data), 201);
    }

    public function updateCategory(Request $request, ProductCategory $category): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:20|unique:product_categories,code,' . $category->id,
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($data);
        return response()->json($category->fresh());
    }

    public function destroyCategory(ProductCategory $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Cannot delete a category that still has products assigned to it.'], 422);
        }
        if ($category->children()->exists()) {
            return response()->json(['message' => 'Cannot delete a category that has sub-categories.'], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }
}
