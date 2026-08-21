<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const KEYS = [
        'company_name', 'company_email', 'company_phone', 'company_address',
        'city', 'country', 'tax_number', 'registration_number',
        'currency', 'currency_symbol', 'financial_year_start',
        'default_tax_rate', 'payment_terms_days', 'invoice_prefix',
        'date_format', 'timezone', 'low_stock_alerts', 'overdue_invoice_alerts',
    ];

    public function index(): JsonResponse
    {
        $settings = SystemSetting::where('group', 'company')->whereNull('branch_id')->pluck('value', 'key');

        $booleanKeys = ['low_stock_alerts', 'overdue_invoice_alerts'];
        $result = [];
        foreach (self::KEYS as $key) {
            $value = $settings[$key] ?? null;
            $result[$key] = in_array($key, $booleanKeys) && $value !== null ? (bool) (int) $value : $value;
        }

        return response()->json($result);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:10',
            'financial_year_start' => 'nullable|string|max:5',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'payment_terms_days' => 'nullable|integer|min:0',
            'invoice_prefix' => 'nullable|string|max:20',
            'date_format' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'low_stock_alerts' => 'nullable|boolean',
            'overdue_invoice_alerts' => 'nullable|boolean',
        ]);

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            SystemSetting::set($key, $value, null, 'company');
        }

        return response()->json(['message' => 'Settings saved successfully.']);
    }
}
