<?php

namespace App\Models\Sys\PaymentMethod;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysPaymentMethodCustomize extends Model
{
    use SoftDeletes;

    protected $table = 'sys_payment_method_customize';

    public function sys_payment_method(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SysPaymentMethod::class, 'sys_payment_method_id', 'id');
    }

    public function scopeNotArchive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeLast($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function getFaMaxAmountAttribute()
    {
//        return ConvertNumbersEntoFa(custom_number_format($this->min_trade_value));
        return ConvertNumbersEntoFa(number_format($this->max_amount));
    }

    public function getFaMinAmountAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->min_amount));
    }

    public function getFaTitlePaymentAttribute()
    {
        return $this->sys_payment_method->title;
    }
}
