<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Branch::query();
        if ($request->input('active_only')) {
            $query->where('is_active', true);
        }
        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string',
            'is_active' => 'boolean',
            'is_head_office' => 'boolean',
            'logo' => 'nullable|file|max:2048|extensions:jpg,jpeg,png,gif,webp',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext  = $file->getClientOriginalExtension();
            $data['logo_path'] = $file->storeAs('branches/logos', Str::random(40).($ext ? '.'.$ext : ''), 'public');
        }
        unset($data['logo']);

        $data['settings'] = $this->mergeInvoiceTemplateSettings([], $request);

        $branch = Branch::create($data);
        return response()->json($branch, 201);
    }

    /**
     * Per-branch invoice-print customization (footer note, bank/payment
     * instructions, the 3 signature line labels) lives inside the existing
     * `settings` JSON column rather than new dedicated columns — it's
     * free-form text content, not structured/queryable data, so a JSON blob
     * fits better than a handful of single-purpose columns.
     */
    private function mergeInvoiceTemplateSettings(array $existingSettings, Request $request): array
    {
        $fields = ['invoice_footer_note', 'invoice_bank_details', 'invoice_signature_label_1', 'invoice_signature_label_2', 'invoice_signature_label_3'];
        if (!array_intersect($fields, array_keys($request->all()))) {
            return $existingSettings;
        }

        $data = $request->validate([
            'invoice_footer_note' => 'nullable|string|max:2000',
            'invoice_bank_details' => 'nullable|string|max:2000',
            'invoice_signature_label_1' => 'nullable|string|max:100',
            'invoice_signature_label_2' => 'nullable|string|max:100',
            'invoice_signature_label_3' => 'nullable|string|max:100',
        ]);

        // A field the caller actually submitted (even as an empty string) means
        // "set it to this" — including deliberately blanking it out, e.g. a
        // branch whose printed invoices never had a bank/cheque section to
        // begin with. Only a genuinely omitted field leaves the existing
        // value untouched.
        $tpl = $existingSettings['invoice_template'] ?? [];
        $sig = $tpl['signature_labels'] ?? [null, null, null];
        if ($request->has('invoice_footer_note'))       $tpl['footer_note']  = $data['invoice_footer_note'] ?? '';
        if ($request->has('invoice_bank_details'))       $tpl['bank_details'] = $data['invoice_bank_details'] ?? '';
        if ($request->has('invoice_signature_label_1'))  $sig[0] = $data['invoice_signature_label_1'] ?? '';
        if ($request->has('invoice_signature_label_2'))  $sig[1] = $data['invoice_signature_label_2'] ?? '';
        if ($request->has('invoice_signature_label_3'))  $sig[2] = $data['invoice_signature_label_3'] ?? '';
        $tpl['signature_labels'] = $sig;

        $existingSettings['invoice_template'] = $tpl;
        return $existingSettings;
    }

    public function show(Branch $branch): JsonResponse
    {
        return response()->json($branch->load('users'));
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:10|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string',
            'is_active' => 'boolean',
            'is_head_office' => 'boolean',
            'logo' => 'nullable|file|max:2048|extensions:jpg,jpeg,png,gif,webp',
            'remove_logo' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($branch->logo_path) Storage::disk('public')->delete($branch->logo_path);
            $file = $request->file('logo');
            $ext  = $file->getClientOriginalExtension();
            $data['logo_path'] = $file->storeAs('branches/logos', Str::random(40).($ext ? '.'.$ext : ''), 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($branch->logo_path) Storage::disk('public')->delete($branch->logo_path);
            $data['logo_path'] = null;
        }
        unset($data['logo'], $data['remove_logo']);

        $data['settings'] = $this->mergeInvoiceTemplateSettings($branch->settings ?? [], $request);

        $branch->update($data);
        return response()->json($branch);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();
        return response()->json(['message' => 'Branch deleted.']);
    }

    public function stats(Branch $branch): JsonResponse
    {
        $today = now()->toDateString();
        $month = now()->format('Y-m');

        return response()->json([
            'sales_today' => $branch->invoices()
                ->whereDate('invoice_date', $today)
                ->where('status', '!=', 'cancelled')
                ->sum('total'),
            'sales_month' => $branch->invoices()
                ->where('invoice_date', 'like', "$month%")
                ->where('status', '!=', 'cancelled')
                ->sum('total'),
            'outstanding_receivables' => $branch->invoices()
                ->whereIn('status', ['confirmed', 'partially_paid'])
                ->sum('balance_due'),
            'pending_expenses' => $branch->expenses()
                ->where('status', 'pending')
                ->count(),
        ]);
    }
}
