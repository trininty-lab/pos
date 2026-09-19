<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:5120',
            'image_crop' => ['nullable', 'json'],
            'remove_image' => ['nullable', 'boolean'],
            'barcode' => 'nullable|unique:products,barcode,'.$this->route('product')->id,
            'sku' => 'nullable|unique:products,sku,'.$this->route('product')->id,
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'buy_price' => 'required|integer|min:0',
            'sell_price' => 'required|integer|min:0|gte:minimum_sell_price',
            'minimum_sell_price' => 'nullable|integer|min:0|gte:buy_price',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ];
    }
}