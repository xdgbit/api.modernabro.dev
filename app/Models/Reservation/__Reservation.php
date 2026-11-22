<?php

namespace App\Models\Reservation;

use App\Models\User\UserCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class __Reservation extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | سیاست اطلاعات مهمانان (guest_info_policy)
    |--------------------------------------------------------------------------
    */
    public const GUEST_INFO_POLICY_NOT_REQUIRED = 'not_required';
    public const GUEST_INFO_POLICY_ALL_AFTER_PAYMENT = 'all_after_payment';
    public const GUEST_INFO_POLICY_ALL_BEFORE_PAYMENT = 'all_before_payment';
    public const GUEST_INFO_POLICY_LEADER_AFTER_PAYMENT = 'leader_after_payment';
    public const GUEST_INFO_POLICY_LEADER_BEFORE_PAYMENT = 'leader_before_payment';
    public const GUEST_INFO_POLICY_ROOM_LEADERS_AFTER_PAYMENT = 'room_leaders_after_payment';
    public const GUEST_INFO_POLICY_ROOM_LEADERS_BEFORE_PAYMENT = 'room_leaders_before_payment';

    /*
    |--------------------------------------------------------------------------
    | وضعیت‌های رزرو
    |--------------------------------------------------------------------------
    */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PRECHECK = 'precheck';
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_AWAITING_GUEST_INFO = 'awaiting_guest_info';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_CHECKED_OUT = 'checked_out';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * انتقال‌های مجاز.
     * توجه: برای precheck، وضعیت واقعی بعد از تأیید بر اساس
     * guest_info_policy تعیین می‌شود (به getAllowedNextStatuses نگاه کنید).
     */
    public const STATUS_TRANSITIONS = [

        // درخواست اولیه رزرو
        self::STATUS_PENDING             => [
            self::STATUS_PRECHECK,
            self::STATUS_AWAITING_PAYMENT,
            self::STATUS_CANCELLED,
        ],

        // بررسی توسط کارشناس / هماهنگی با اقامتگاه
        self::STATUS_PRECHECK            => [
            self::STATUS_AWAITING_PAYMENT,
            self::STATUS_AWAITING_GUEST_INFO,
            self::STATUS_CANCELLED,
        ],

        // منتظر پرداخت (مهلت ۲۰ دقیقه)
        // بعد از پرداخت: سیاست‌های after_payment -> awaiting_guest_info
        //               سیاست not_required      -> confirmed
        self::STATUS_AWAITING_PAYMENT    => [
            self::STATUS_AWAITING_GUEST_INFO,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
        ],

        // منتظر اطلاعات مهمان
        // (سیاست before_payment: ورود اطلاعات + پرداخت در همان مهلت ۲۰ دقیقه)
        self::STATUS_AWAITING_GUEST_INFO => [
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
        ],

        // رزرو نهایی
        self::STATUS_CONFIRMED           => [
            self::STATUS_CHECKED_IN,
            self::STATUS_CANCELLED,
        ],

        // ورود مهمان
        self::STATUS_CHECKED_IN          => [
            self::STATUS_CHECKED_OUT,
        ],

        // خروج مهمان
        self::STATUS_CHECKED_OUT         => [
            self::STATUS_COMPLETED,
        ],

        // پایان رزرو
        self::STATUS_COMPLETED           => [],

        // لغو شده
        self::STATUS_CANCELLED           => [],

        // منقضی شده
    ];

    /*
    |--------------------------------------------------------------------------
    | پرداخت
    |--------------------------------------------------------------------------
    */
    public const PAYMENT_METHOD_ONLINE = 'online';
    public const PAYMENT_METHOD_ON_SITE = 'on_site';

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';
    public const PAYMENT_STATUS_CANCELLED = 'cancelled';
    public const PAYMENT_STATUS_NOT_REQUIRED = 'not_required';

    public const PAYMENT_STATUS_TRANSITIONS = [

        self::PAYMENT_STATUS_PENDING => [
            self::PAYMENT_STATUS_PAID,
            self::PAYMENT_STATUS_FAILED,
            self::PAYMENT_STATUS_CANCELLED,
            self::PAYMENT_STATUS_NOT_REQUIRED,
        ],

        self::PAYMENT_STATUS_PAID => [
            self::PAYMENT_STATUS_REFUNDED,
        ],

        self::PAYMENT_STATUS_FAILED => [
            self::PAYMENT_STATUS_PENDING,
            self::PAYMENT_STATUS_CANCELLED,
        ],

        self::PAYMENT_STATUS_REFUNDED => [],

        self::PAYMENT_STATUS_CANCELLED => [],

        self::PAYMENT_STATUS_NOT_REQUIRED => [],
    ];

    protected $appends = [
        'full_name',
        'status_label',
        'status_icon',
        'status_class',
        'jalali_check_in',
        'jalali_check_out',
        'guest_info_save_url',
        'fa_payment_price',
        'fa_guest_info_policy',
    ];

    protected $fillable = [
        'code',
        'user_customer_id',
        'check_in',
        'check_out',
        'guest_count',
        'nights',
        'total_amount',
        'status',
        'description',
        'confirmed_at',
        'cancelled_at',

        'submitted_at',
        'submitted_by',

        // سیاست اطلاعات مهمان
        'guest_info_policy',

        // پرداخت
        'payment_type',
        'payment_status',
        'payment_price',
        'deposit_percent',
        'deposit_amount',

        // مهلت‌ها و زمان‌ها
        'payment_expired_at',
        'guest_info_expired_at',
        'precheck_expired_at',
        'precheck_approved_at',
    ];

    protected $casts = [
        'check_in'              => 'date',
        'check_out'             => 'date',
        'guest_count'           => 'integer',
        'nights'                => 'integer',
        'unit_price'            => 'decimal:2',
        'total_amount'          => 'decimal:2',
        'deposit_percent'       => 'integer',
        'deposit_amount'        => 'decimal:2',
        'confirmed_at'          => 'datetime',
        'cancelled_at'          => 'datetime',
        'payment_expired_at'    => 'datetime',
        'guest_info_expired_at' => 'datetime',
        'precheck_expired_at'   => 'datetime',
        'precheck_approved_at'  => 'datetime', // زمان تأیید اولیه کارشناس
    ];

    /*
    |--------------------------------------------------------------------------
    | منطق سیاست اطلاعات مهمان و وضعیت‌ها
    |--------------------------------------------------------------------------
    */

    /**
     * آیا اطلاعات مهمان باید قبل از پرداخت وارد شود؟
     */
    public function requiresGuestInfoBeforePayment(): bool
    {
        return str_ends_with((string)$this->guest_info_policy, '_before_payment');
    }

    /**
     * وضعیتی که بعد از تأیید کارشناس باید ست شود.
     */
    public function approvalStatus(): string
    {
        return $this->requiresGuestInfoBeforePayment()
            ? self::STATUS_AWAITING_GUEST_INFO
            : self::STATUS_AWAITING_PAYMENT;
    }

    /**
     * وضعیت‌های مجاز بعدی.
     * (منبع اصلی ReservationStatusService::allowedStatuses است و این متد همان منطق را دارد)
     */
    public function getAllowedNextStatuses(): array
    {
        if (
            $this->status === self::STATUS_AWAITING_PAYMENT
            && !str_ends_with(
                (string)$this->guest_info_policy,
                '_after_payment'
            )
        )
        {
            return [
                self::STATUS_CONFIRMED,
                self::STATUS_CANCELLED,
            ];
        }

        if (
            in_array($this->status, [
                self::STATUS_PRECHECK,
                self::STATUS_AWAITING_PAYMENT,
            ], true)
            && $this->guest_info_created_at
        )
        {
            return [
                self::STATUS_CONFIRMED,
                self::STATUS_CANCELLED,
            ];
        }

        if ($this->status === self::STATUS_PRECHECK)
        {
            return [
                $this->approvalStatus(),
                self::STATUS_CANCELLED,
            ];
        }

        return self::STATUS_TRANSITIONS[ $this->status ] ?? [];
    }

    /**
     * آیا مهلت وضعیت فعلی گذشته است؟
     */
    public function isDeadlineExpired(): bool
    {
        return match ($this->status)
        {
            self::STATUS_AWAITING_PAYMENT => $this->payment_expired_at?->isPast() ?? false,
            self::STATUS_AWAITING_GUEST_INFO => $this->guest_info_expired_at?->isPast() ?? false,
            self::STATUS_PRECHECK => $this->precheck_expired_at?->isPast() ?? false,
            default => false,
        };
    }

    public static function generate_code(): string
    {
        do
        {
            $code = 'RS-' . JDate('Ymd', 'en') . '-' . rand(1000, 9999);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */
    public function user_customer(): BelongsTo
    {
        return $this->belongsTo(
            UserCustomer::class,
            'user_customer_id'
        );
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(
            ReservationRoom::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getGuestInfoSaveUrlAttribute()
    {
        return route('frontend.accommodation.index');
    }

    public function getFaGuestInfoPolicyAttribute()
    {
        return match ($this->guest_info_policy)
        {
            self::GUEST_INFO_POLICY_NOT_REQUIRED => 'لازم نیست',
            self::GUEST_INFO_POLICY_ALL_AFTER_PAYMENT => 'همه بعد از پرداخت',
            self::GUEST_INFO_POLICY_ALL_BEFORE_PAYMENT => 'همه قبل از پرداخت',
            self::GUEST_INFO_POLICY_LEADER_AFTER_PAYMENT => 'سرپرست بعد از پرداخت',
            self::GUEST_INFO_POLICY_LEADER_BEFORE_PAYMENT => 'سرپرست قبل از پرداخت',
            self::GUEST_INFO_POLICY_ROOM_LEADERS_AFTER_PAYMENT => 'سرپرست هر اتاق بعد از پرداخت',
            self::GUEST_INFO_POLICY_ROOM_LEADERS_BEFORE_PAYMENT => 'سرپرست هر اتاق قبل از پرداخت',
            default => '-',
        };
    }

    public function getJalaliCheckInAttribute()
    {
        return $this->check_in
            ? ConvertNumbersEntoFa(verta($this->check_in)->format('Y-m-d'))
            : null;
    }

    public function getJalaliSubmittedAtAttribute()
    {
        return $this->submitted_at ? ConvertNumbersEntoFa(verta($this->submitted_at) . '') : null;
    }

    public function getJalaliCheckOutAttribute()
    {
        return $this->check_out
            ? ConvertNumbersEntoFa(verta($this->check_out)->format('Y-m-d'))
            : null;
    }

    public function getFaTotalAmountAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->total_amount));
    }

    public function getFaPaymentPriceAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->payment_price));
    }

    public function getFaNightsAttribute()
    {
        return ConvertNumbersEntoFa($this->nights);
    }

    public function getFullNameAttribute()
    {
        return $this->user_customer?->full_name;
    }

    public function getStatusLabelAttribute()
    {
        // مهلت گذشته ولی هنوز توسط scheduler منقضی نشده
        if ($this->isDeadlineExpired())
        {
            return match ($this->status)
            {
                self::STATUS_PRECHECK => 'بررسی منقضی شده',
                self::STATUS_AWAITING_PAYMENT => 'پرداخت منقضی شده',
                self::STATUS_AWAITING_GUEST_INFO => 'مهلت ورود اطلاعات منقضی شده',
                default => '__!!!!__',
            };
        }

        return match ($this->status)
        {
            self::STATUS_PENDING => 'در انتظار',
            self::STATUS_PRECHECK => 'در انتظار هماهنگی',
            self::STATUS_AWAITING_PAYMENT => 'در انتظار پرداخت',
            self::STATUS_AWAITING_GUEST_INFO => 'در انتظار اطلاعات مهمان',
            self::STATUS_CONFIRMED => 'تایید شده',
            self::STATUS_CHECKED_IN => 'تحویل شده',
            self::STATUS_CHECKED_OUT => 'تخلیه شده',
            self::STATUS_COMPLETED => 'تکمیل شده',
            self::STATUS_CANCELLED => 'لغو شده',
            default => '__!!!!__',
        };
    }

    public function getStatusClassAttribute(): string
    {
        if ($this->isDeadlineExpired())
        {
            return 'bg-slate-100 text-slate-500';
        }

        return match ($this->status)
        {
            self::STATUS_PENDING => 'bg-amber-50 text-amber-600',
            self::STATUS_PRECHECK => 'bg-cyan-50 text-cyan-600',
            self::STATUS_AWAITING_PAYMENT => 'bg-orange-50 text-orange-600',
            self::STATUS_AWAITING_GUEST_INFO => 'bg-yellow-50 text-yellow-600',
            self::STATUS_CONFIRMED => 'bg-emerald-50 text-emerald-600',
            self::STATUS_CHECKED_IN => 'bg-blue-50 text-blue-600',
            self::STATUS_CHECKED_OUT => 'bg-violet-50 text-violet-600',
            self::STATUS_COMPLETED => 'bg-slate-100 text-slate-500',
            self::STATUS_CANCELLED => 'bg-red-50 text-red-600',
            default => 'bg-slate-100 text-slate-500',
        };
    }

    public function getStatusIconAttribute(): string
    {
        if ($this->isDeadlineExpired())
        {
            return 'XCircle';
        }

        return match ($this->status)
        {
            self::STATUS_PENDING => 'Clock3',
            self::STATUS_PRECHECK => 'PhoneCall',
            self::STATUS_AWAITING_PAYMENT => 'CreditCard',
            self::STATUS_AWAITING_GUEST_INFO => 'UserRound',
            self::STATUS_CONFIRMED => 'CheckCircle2',
            self::STATUS_CHECKED_IN => 'LogIn',
            self::STATUS_CHECKED_OUT => 'LogOut',
            self::STATUS_COMPLETED => 'CheckCircle2',
            self::STATUS_CANCELLED => 'XCircle',
            default => 'Clock3',
        };
    }

    protected function guestInfoNotRequired(Reservation $r): bool
    {
        return $r->guest_info_policy === 'not_required';
    }

    protected function guestInfoAfterPayment(Reservation $r): bool
    {
        return str_ends_with((string)$r->guest_info_policy, '_after_payment');
    }

    protected function guestInfoBeforePayment(Reservation $r): bool
    {
        return str_ends_with((string)$r->guest_info_policy, '_before_payment');
    }

}