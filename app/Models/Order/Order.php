<?php

namespace App\Models\Order;

use App\Models\User\Customer\UserCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PAID       = 'paid';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED    = 'shipped';
    public const STATUS_DELIVERED  = 'delivered';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_RETURNED   = 'returned';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
        self::STATUS_RETURNED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING    => 'در انتظار',
        self::STATUS_PAID       => 'پرداخت شده',
        self::STATUS_PROCESSING => 'در حال پردازش',
        self::STATUS_SHIPPED    => 'ارسال شده',
        self::STATUS_DELIVERED  => 'تحویل شده',
        self::STATUS_CANCELLED  => 'لغو شده',
        self::STATUS_RETURNED   => 'مرجوع شده',
    ];

    public const PAYMENT_UNPAID         = 'unpaid';
    public const PAYMENT_PAID           = 'paid';
    public const PAYMENT_PARTIALLY_PAID = 'partially_paid';
    public const PAYMENT_REFUNDED       = 'refunded';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID,
        self::PAYMENT_PAID,
        self::PAYMENT_PARTIALLY_PAID,
        self::PAYMENT_REFUNDED,
    ];


    /*
    |--------------------------------------------------------------------------
    | Mass Assignment & Casts
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'code',
        'user_customer_id',
        'status',
        'payment_status',
        'payment_method_id',
        'shipping_method_id',
        'discount_code_id',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'total_amount',
        'receiver_name',
        'receiver_mobile',
        'shipping_address',
        'postal_code',
        'tracking_code',
        'description',
        'admin_note',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal'        => 'integer',
        'discount_amount' => 'integer',
        'shipping_cost'   => 'integer',
        'total_amount'    => 'integer',
        'paid_at'         => 'datetime',
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function userCustomer(): BelongsTo
    {
        return $this->belongsTo(UserCustomer::class, 'user_customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * تاریخ ثبت سفارش به شمسی، برای نمایش در جدول و پنل
     */
    public function getJalaliCreatedAtAttribute(): ?string
    {
        return $this->created_at ? ConvertNumbersEntoFa(verta($this->created_at) . '') : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isFinal(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_RETURNED], true);
    }
}