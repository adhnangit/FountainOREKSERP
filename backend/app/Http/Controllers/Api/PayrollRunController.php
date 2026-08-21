<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Services\BranchContextService;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollRunController extends Controller
{
    public function __construct(
        private PayrollService $payroll,
        private BranchContextService $branchContext
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = PayrollRun::with('branch')->withCount('payslips');
        $this->branchContext->applyScope($q);
        if ($request->year) $q->where('year', $request->year);
        if ($request->status) $q->where('status', $request->status);

        return response()->json($q->orderByDesc('year')->orderByDesc('month')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'notes' => 'nullable|string',
        ]);

        if (PayrollRun::where('branch_id', $data['branch_id'])->where('month', $data['month'])->where('year', $data['year'])->exists()) {
            return response()->json(['message' => 'A payroll run already exists for this branch and period.'], 422);
        }

        $run = PayrollRun::create([
            'branch_id' => $data['branch_id'],
            'month' => $data['month'],
            'year' => $data['year'],
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
            // Explicit, not left to the DB column default — Eloquent's create() doesn't
            // re-read DB-level defaults back into the in-memory model, so generateRun()'s
            // status === 'draft' check would otherwise see null and reject a run that was
            // actually inserted as 'draft' just fine.
            'status' => 'draft',
        ]);

        $this->payroll->generateRun($run);

        return response()->json($this->loadRun($run), 201);
    }

    public function show(PayrollRun $payrollRun): JsonResponse
    {
        return response()->json($this->loadRun($payrollRun));
    }

    public function regenerate(PayrollRun $payrollRun): JsonResponse
    {
        if ($payrollRun->status !== 'draft') {
            return response()->json(['message' => 'Only a draft payroll run can be regenerated.'], 422);
        }

        $this->payroll->generateRun($payrollRun);
        return response()->json($this->loadRun($payrollRun));
    }

    public function markPaid(Request $request, PayrollRun $payrollRun): JsonResponse
    {
        if ($payrollRun->status !== 'draft') {
            return response()->json(['message' => 'This payroll run has already been marked as paid.'], 422);
        }

        $data = $request->validate(['notes' => 'nullable|string']);

        $payrollRun->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => auth()->id(),
            'notes' => $data['notes'] ?? $payrollRun->notes,
        ]);

        return response()->json($this->loadRun($payrollRun));
    }

    public function destroy(PayrollRun $payrollRun): JsonResponse
    {
        if ($payrollRun->status !== 'draft') {
            return response()->json(['message' => 'A paid payroll run cannot be deleted.'], 422);
        }

        $payrollRun->delete();
        return response()->json(['message' => 'Payroll run deleted.']);
    }

    public function payslipPdf(Payslip $payslip)
    {
        $payslip->load(['employee.branch', 'employee.department', 'employee.designation', 'payrollRun']);
        $pdf = Pdf::loadView('pdf.payslip', ['payslip' => $payslip]);
        $period = str_pad($payslip->payrollRun->month, 2, '0', STR_PAD_LEFT) . '-' . $payslip->payrollRun->year;
        return $pdf->download("payslip-{$payslip->employee->employee_code}-{$period}.pdf");
    }

    private function loadRun(PayrollRun $run): PayrollRun
    {
        return $run->load(['branch', 'payslips' => fn($q) => $q->with('employee:id,first_name,last_name,employee_code')->orderBy('id')]);
    }
}
