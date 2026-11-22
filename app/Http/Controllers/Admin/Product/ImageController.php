<?php

namespace App\Http\Controllers\Admin\Product;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Classes\LFM;
use App\Models\Product\Product;
use App\Models\Product\ProductFile;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function upload(Request $request, Product $product)
    {
        $request->validate([
            'images'   => ['required', 'array'],
            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $files = [];
        foreach ($request->file('images') as $image)
        {
            $file = LFM::fileUpload(
                $image,
                'product_img',
                'user_expert'
            );
            $productFile = $product->files()
                ->create([
                    'file_id'    => $file->id,
                    'is_cover'   => false,
                    'sort_order' => $product->files()->count(),
                ]);

            $files[] = [
                'id'                     => $productFile->id,
                'file_id'                => $file->id,
                'file_cropped_src'       => LFM::downloadLink($file->id),
                'file_original_size_src' => LFM::downloadLink($file->id),
                'is_cover'               => $productFile->is_cover,
            ];
        }
        if (
            !$product->files()
                ->where('is_cover', true)
                ->exists())
        {
            $product->files()
                ->orderBy('sort_order')
                ->first()
                ?->update([
                    'is_cover' => true,
                ]);
        }

        return ApiResponse::success($files);
    }

    public function list(Product $product)
    {
        $data = $product->files;

        return ApiResponse::success($data);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        Product     $product,
        ProductFile $productFile
    )
    {
        abort_unless(
            $productFile->product_id === $product->id,
            404
        );

        $wasCover = $productFile->is_cover;

        $productFile->delete();

        /*
        |--------------------------------------------------------------------------
        | If cover deleted, set first remaining image as cover
        |--------------------------------------------------------------------------
        */

        if ($wasCover)
        {
            $product->files()
                ->orderBy('sort_order')
                ->first()
                ?->update([
                    'is_cover' => true,
                ]);
        }

        return ApiResponse::success();
    }


    /*
    |--------------------------------------------------------------------------
    | Cover
    |--------------------------------------------------------------------------
    */

    public function cover(
        Product     $product,
        ProductFile $productFile
    )
    {
        abort_unless(
            $productFile->product_id === $product->id,
            404
        );

        $product->files()
            ->update([
                'is_cover' => false,
            ]);

        $productFile->update([
            'is_cover' => true,
        ]);

        return ApiResponse::success();
    }

    /*
|--------------------------------------------------------------------------
| Order
|--------------------------------------------------------------------------
*/
    public function order(Request $request, Product $product)
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $files = $product->files()
            ->whereIn('id', $validated['ids'])
            ->get();

        abort_if(
            $files->count() !== count($validated['ids']),
            422,
            'برخی از تصاویر متعلق به این محصول نیستند.'
        );

        \DB::transaction(function () use ($product, $validated) {

            foreach ($validated['ids'] as $index => $id)
            {
                $product->files()
                    ->where('id', $id)
                    ->update([
                        'sort_order' => $index,
                    ]);
            }

        });

        return ApiResponse::success(
            $product->files()
                ->orderBy('sort_order')
                ->get()
        );
    }
}
