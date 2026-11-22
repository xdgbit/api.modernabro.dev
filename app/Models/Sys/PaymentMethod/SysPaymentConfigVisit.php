<?php

namespace App\Models\Sys\PaymentMethod;

use App\Models\User\Customer\UserCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysPaymentConfigVisit extends Model
{
    use SoftDeletes;

    protected $table = 'sys_payment_config_visits';

    public function user_customer()
    {
        return $this->belongsTo(UserCustomer::class, 'user_customer_id', 'id');
    }

//    public function sys_payment_config()
//    {
//        return $this->belongsTo(SysPaymentConfig::class, 'sys_payment_config_id', 'id');
//    }

    public function online_payment_config()
    {
        return $this->belongsTo(SysOnlinePaymentConfig::class, 'origin_id', 'id');
    }
    public function direct_payment_config()
    {
        return $this->belongsTo(SysDirectPaymentConfig::class, 'origin_id', 'id');
    }

    public function getJalaliSeenAtAttribute()
    {
        return $this->seen_at ? ConvertNumbersEntoFa(verta($this->seen_at) . '') : null;
    }





}
