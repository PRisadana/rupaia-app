<?php

namespace App\Http\Requests;

use App\Models\License;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['content', 'bundle'])],
            'tag' => ['nullable', 'integer', 'exists:tags,id'],
            'license' => [
                'nullable',
                'integer',
                Rule::exists((new License)->getTable(), 'id')->where('is_active', true),
            ],
            'seller' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'seller'),
            ],
            'sale_type' => ['nullable', Rule::in(['single_sale', 'multi_sale'])],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => array_filter([
                'nullable',
                'integer',
                'min:0',

                $this->filled('min_price') ? 'gte:min_price' : null,
            ]),
        ];
    }

    public function messages(): array
    {
        return [
            'max_price.gte' => 'Maximum price must be greater than or equal to the minimum price.',
            'min_price.integer' => 'Minimum price must be a whole number in Rupiah.',
            'max_price.integer' => 'Maximum price must be a whole number in Rupiah.',
        ];
    }
}
