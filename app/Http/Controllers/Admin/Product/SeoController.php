<?php

namespace App\Http\Controllers\Admin\Product;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class SeoController extends Controller
{
    public function show(Product $product)
    {
        return ApiResponse::success([
            'slug'             => $product->slug,
            'meta_title'       => $product->meta_title,
            'meta_description' => $product->meta_description,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'slug'             => ['required', 'string', 'max:255'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ]);

        $slug = $this->normalizeSlug($validated['slug']);

        if (!$slug)
        {
            throw ValidationException::withMessages([
                'slug' => 'نامک وارد شده معتبر نیست.',
            ]);
        }

        $exists = Product::where('slug', $slug)
            ->where('id', '!=', $product->id)
            ->exists();

        if ($exists)
        {
            throw ValidationException::withMessages([
                'slug' => 'این نامک قبلاً برای محصول دیگری استفاده شده است.',
            ]);
        }

        $product->slug = $slug;
        $product->meta_title = $validated['meta_title'] ?? null;
        $product->meta_description = $validated['meta_description'] ?? null;

        $product->save();

        return ApiResponse::success(
            [
                'seo' => $product->only([
                    'slug',
                    'meta_title',
                    'meta_description',
                ]),
            ],
            'تنظیمات سئو با موفقیت ذخیره شد.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Slug
    |--------------------------------------------------------------------------
    | اجازه به حروف فارسی، انگلیسی، عدد و خط تیره
    |--------------------------------------------------------------------------
    */

    private function normalizeSlug(string $value): string
    {
        $value = trim(mb_strtolower($value));

        $value = preg_replace('/[\s_]+/u', '-', $value);
        $value = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $value);
        $value = preg_replace('/-+/', '-', $value);

        return trim($value, '-');
    }
}
