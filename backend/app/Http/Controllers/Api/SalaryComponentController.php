<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryComponent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    public function index(Employee $employee): JsonResponse
    {
        return response()->json($employee->salaryComponents()->orderBy('type')->orderBy('name')->get());
    }

    public function store(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction',
            'amount' => 'required|numeric|min:0',
        ]);
        $data['employee_id'] = $employee->id;

        return response()->json(SalaryComponent::create($data), 201);
    }

    public function update(Request $request, SalaryComponent $salaryComponent): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:allowance,deduction',
            'amount' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $salaryComponent->update($data);
        return response()->json($salaryComponent->fresh());
    }

    public function destroy(SalaryComponent $salaryComponent): JsonResponse
    {
        $salaryComponent->delete();
        return response()->json(['message' => 'Salary component deleted.']);
    }
}
