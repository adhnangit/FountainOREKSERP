<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $expenses) {}

    public function collection(): Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return ['Date', 'Expense #', 'Account / Category', 'Description', 'Reference', 'Payment Method', 'Amount', 'Status', 'Submitted By', 'Branch'];
    }

    public function map($expense): array
    {
        return [
            optional($expense->expense_date)->format('Y-m-d'),
            $expense->expense_number,
            $expense->account?->name ?? $expense->category?->name ?? '—',
            $expense->description,
            $expense->reference_number ?? '—',
            $expense->payment_method,
            (float) $expense->amount,
            $expense->status,
            $expense->createdBy?->name ?? '—',
            $expense->branch?->name ?? '—',
        ];
    }
}
