<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'      => ['nullable', 'string', 'max:255'],
            'status'      => ['nullable', 'in:active,inactive'],
            'brand_id'    => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'in_stock'    => ['nullable', 'boolean'],
            'sort'        => ['nullable', 'in:id,name,price,stock,created_at'],
            'direction'   => ['nullable', 'in:asc,desc'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}