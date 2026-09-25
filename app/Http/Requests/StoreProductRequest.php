<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'minimum_sell_price' => $this->input('minimum_sell_price', $this->input('sell_price')),
            'maximum_sell_price' => $this->input('maximum_sell_price', $this->input('sell_price')),
        ]);
    }

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
            'barcode' => 'nullable|unique:products,barcode',
            'sku' => 'nullable|unique:products,sku',
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'buy_price' => 'required|integer|min:0',
            'sell_price' => 'required|integer|min:0|gte:minimum_sell_price',
            'minimum_sell_price' => 'nullable|integer|min:0|gte:buy_price',
            'maximum_sell_price' => 'nullable|integer|min:0|gte:sell_price',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ];
    }
}