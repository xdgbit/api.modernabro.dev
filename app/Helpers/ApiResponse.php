<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'عملیات با موفقیت انجام شد.',
        int $status = 200,
        ?array $meta = null
    ): JsonResponse {
        // اگر داده صفحه‌بندی باشد، data و meta را جدا می‌کنیم
        if ($data instanceof AbstractPaginator) {
            $meta = self::paginationMeta($data);
            $data = $data->items();
        }

        // اگر Resource یا ResourceCollection باشد، خروجی آرایه‌ای آن را می‌گیریم
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            $resolved = $data->resolve();

            // ResourceCollection صفحه‌بندی‌شده: meta خود resource را هم می‌گیریم
            if ($data instanceof ResourceCollection && $data->resource instanceof AbstractPaginator) {
                $meta ??= self::paginationMeta($data->resource);
            }

            $data = $resolved;
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'errors'  => null,
            'meta'    => $meta,
        ], $status);
    }

    public static function error(
        string $message = 'خطایی رخ داده است.',
        mixed $errors = null,
        int $status = 400,
        ?array $meta = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'data'    => null,
            'message' => $message,
            'errors'  => $errors,
            'meta'    => $meta,
        ], $status);
    }

    /**
     * اطلاعات صفحه‌بندی برای meta
     */
    private static function paginationMeta(AbstractPaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator instanceof LengthAwarePaginator ? $paginator->total() : null,
            'last_page'    => $paginator instanceof LengthAwarePaginator ? $paginator->lastPage() : null,
        ];
    }
}