<?php

namespace App\Repositories\Product;

use App\Models\Product\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    private const SORTABLE = ['id', 'name', 'price', 'stock', 'created_at'];

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['brand:id,title,slug', 'category:id,title,slug']);

        if (!empty($filters['status']))
        {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['brand_id']))
        {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['category_id']))
        {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search']))
        {
            $term = '%' . $filters['search'] . '%';

            $query->where(fn($q) => $q
                ->where('name', 'like', $term)
                ->orWhere('sku', 'like', $term));
        }

        if (!empty($filters['in_stock']))
        {
            $query->where('stock', '>', 0);
        }

        $sort = in_array($filters['sort'] ?? null, self::SORTABLE, true)
            ? $filters['sort']
            : 'created_at';

        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sort, $direction)->paginate($perPage);
    }
}