<?php

namespace App\Models\Product;

use App\Http\Helpers\Classes\LFM;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'sku',
        'description',
        'brand_id',
        'category_id',
        'price',
        'sale_price',
        'stock',
        'low_stock_threshold',
        'status',
        'slug',
        'meta_description',
        'meta_title',
        'star_rating',
    ];

    protected $casts = [
        'price'               => 'integer',
        'sale_price'          => 'integer',
        'stock'               => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function files()
    {
        return $this->hasMany(ProductFile::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function getDisplayRatingAttribute(): ?float
    {
        if ($this->star_rating > 0)
        {
            return (float)$this->star_rating;
        }

        return $this->rating_score;
    }

    public function getRatingScoreAttribute(): ?float
    {
        $avg = $this->relationLoaded('reviews')
            ? $this->reviews->avg('rating')
            : $this->reviews()->avg('rating');

        return $avg !== null ? round((float)$avg, 1) : null;
    }

    public function getCoverImageAttribute()
    {
        $file = $this->files()
            ->where('is_cover', true)
            ->first();

        return $file ? LFM::downloadLink($file->file_id, '404.jpg') : null;
    }

    public function getFinalPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentAttribute()
    {
        return $this->price > 0 && $this->sale_price !== null
        && $this->sale_price < $this->price
            ? (int)round(
                (($this->price - $this->sale_price) / $this->price) * 100
            )
            : 0;
    }
    /**
     * اقلام سفارش که این محصول در آن‌ها استفاده شده.
     * مدل OrderItem را مطابق نام واقعی پروژه تنظیم کنید.
     * //     */
//    public function orderItems(): HasMany
//    {
//        return $this->hasMany(\App\Models\Order\OrderItem::class, 'product_id');
//    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->low_stock_threshold;
    }
}