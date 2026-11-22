<?php

namespace App\Models\Accommodation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function ConvertNumbersEntoFa;

class AccommodationUnitPrice extends Model
{
    protected $table = 'accommodation_unit_prices';

    protected $appends = [
        'fa_is_active',
        'fa_type',
        'fa_ends_at',
        'fa_starts_at',

        'date_starts_at',
        'date_starts_at',

        'fa_price',
        'fa_extra_price',

    ];
    protected $fillable = [
        'accommodation_unit_id',
        'title',
        'priority',
        'price',
        'extra_price',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',

        'price' => 'decimal:2',

        'extra_guest_price' => 'decimal:2',

        'starts_at' => 'date',
        'ends_at' => 'date',

        'is_active' => 'boolean',
        'weekdays' => 'array',

    ];

    public function getFaStartsAtAttribute()
    {
        return $this->starts_at ? ConvertNumbersEntoFa(verta($this->starts_at)->format('Y-m-d') . '') : null;
    }

    public function getFaEndsAtAttribute()
    {
        return $this->ends_at ? ConvertNumbersEntoFa(verta($this->ends_at)->format('Y-m-d') . '') : null;
    }

    public function getDateStartsAtAttribute()
    {
        return $this->starts_at ? $this->starts_at->format('Y-m-d') . '' : null;
    }

    public function getDateEndsAtAttribute()
    {
        return $this->ends_at ? $this->ends_at->format('Y-m-d') . '' : null;
    }

    public function getFaIsActiveAttribute()
    {
        if ($this->is_active)
        {
            return 'فعال';
        }
        else
        {
            return 'غیرفعال';
        }
    }

    public function getFaTypeAttribute()
    {
        switch ($this->type)
        {
            case 'base':
                return 'قیمت پایه';
            case 'season':
                return 'فصلی';
            case 'weekend':
                return 'آخر هفته';
            case 'holiday':
                return 'تعطیلات';
            default:
                return '__!!!!__';
        }
    }

    public function getFaPriceAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->price));
    }
    public function getFaExtraPriceAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->extra_price));
    }
}