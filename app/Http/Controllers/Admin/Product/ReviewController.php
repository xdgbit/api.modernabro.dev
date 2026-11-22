<?php

namespace App\Http\Controllers\Admin\Product;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product\ProductReview;

class ReviewController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected'];

    private const ALLOWED_COLUMNS = [
        'id',
        'rating',
        'created_at',
        'actioned_type',
    ];

    /*
    |--------------------------------------------------------------------------
    | Datatable
    |--------------------------------------------------------------------------
    */

    public function datatable(Request $request)
    {
        $search = trim((string)$request->input('search', ''));
        $status = $request->input('actioned_type');
        $rating = $request->input('rating');
        $productId = $request->input('product_id');
        $columnName = $request->input('column_name', 'id');
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $length = min(max((int)$request->input('length', 10), 1), 100);

        if (!in_array($columnName, self::ALLOWED_COLUMNS, true))
        {
            $columnName = 'id';
        }

        $query = ProductReview::query()
            ->with('product:id,name');

        if ($search !== '')
        {
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($a) use ($search) {
                        $a->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($status, self::STATUSES, true))
        {
            $query->where('actioned_type', $status);
        }

        if (is_numeric($rating) && (int)$rating >= 1 && (int)$rating <= 5)
        {
            $query->where('rating', (int)$rating);
        }

        if (is_numeric($productId))
        {
            $query->where('product_id', (int)$productId);
        }

        $reviews = $query
            ->orderBy($columnName, $dir)
            ->paginate($length);

        $reviews->getCollection()->transform(function ($review) {
            return $this->formatReview($review);
        });

        return ApiResponse::success($reviews);
    }


    /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */

    public function stats()
    {
        $counts = ProductReview::query()
            ->selectRaw('actioned_type, COUNT(*) as total')
            ->groupBy('actioned_type')
            ->pluck('total', 'actioned_type');

        $approvedQuery = ProductReview::query()->where('actioned_type', 'approved');

        $pendingReviews = ProductReview::query()
            ->with('product:id,name')
            ->where('actioned_type', 'pending')
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(fn($review) => $this->formatReview($review))
            ->values();

        return ApiResponse::success([
            'total'           => (int)$counts->sum(),
            'approved'        => (int)($counts['approved'] ?? 0),
            'pending'         => (int)($counts['pending'] ?? 0),
            'rejected'        => (int)($counts['rejected'] ?? 0),
            'average_rating'  => round((float)$approvedQuery->avg('rating'), 1),
            'products_count'  => (int)$approvedQuery
                ->distinct('product_id')
                ->count('product_id'),
            'pending_reviews' => $pendingReviews,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Approve / Reject
    |--------------------------------------------------------------------------
    */

    public function approve(int $id)
    {
        return $this->changeStatus($id, 'approved');
    }

    public function reject(int $id)
    {
        return $this->changeStatus($id, 'rejected');
    }

    private function changeStatus(int $id, string $status)
    {
        $review = ProductReview::findOrFail($id);

        $review->update([
            'actioned_type' => $status,
            'actioned_at'   => now(),
            'actioned_by'   => auth('user_expert')->id(),
        ]);

        return ApiResponse::success([
            'id'            => $review->id,
            'actioned_type' => $review->actioned_type,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function formatReview(ProductReview $review): array
    {
        return [
            'id'                  => $review->id,
            'product_id'          => $review->product_id,
            'product_name'        => $review->product?->name,
            'jalali_submitted_at' => $review->jalali_submitted_at,
            'author_name'         => $review->author_full_name,
            'rating'              => (int)$review->rating,
            'comment'             => $review->comment,
            'actioned_type'       => $review->actioned_type,
            'created_at'          => optional($review->created_at)->format('Y-m-d H:i'),
        ];
    }
}