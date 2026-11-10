<?php

namespace App\Http\Requests;

use App\Models\DistributorExternalSale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDistributorExternalSaleRequest extends FormRequest
{
    /** Toleransi pembulatan antara total kiriman dan hasil hitung item, dalam Rupiah. */
    private const TOTAL_TOLERANCE = 1.0;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_order_number' => ['required', 'string', 'max:100'],
            'sold_at' => ['required', 'date'],
            'status' => ['nullable', Rule::in([DistributorExternalSale::STATUS_CONFIRMED, DistributorExternalSale::STATUS_CANCELLED])],
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.code' => ['nullable', 'string', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.product_code' => ['required', 'string', 'max:100'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.uom' => ['nullable', 'string', 'max:20'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $subtotal = collect($this->input('items'))
                    ->sum(fn ($item) => round((float) $item['quantity'] * (float) $item['unit_price'], 2));
                $expected = $subtotal - (float) $this->input('discount', 0) + (float) $this->input('tax', 0);

                if (abs($expected - (float) $this->input('total')) > self::TOTAL_TOLERANCE) {
                    $validator->errors()->add('total', 'Total tidak cocok dengan item, diskon, dan pajak. Hasil hitung: ' . number_format($expected, 2, '.', ''));
                }
            },
        ];
    }
}
