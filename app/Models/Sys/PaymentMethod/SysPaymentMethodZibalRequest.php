<?php

namespace App\Models\Sys\PaymentMethod;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysPaymentMethodZibalRequest extends Model
{
    use SoftDeletes;

    protected $table = 'sys_payment_method_zibal_requests';

    public function getCallBackUrlAttribute()
    {
        return route('callback.online_payment.zibal', ['invoice_tracking_code' => $this->invoice_tracking_code]);
    }

    public function getRialPriceAttribute()
    {
        return $this->price * 10;
    }

    public function getFaPayRequestErrorAttribute()
    {
        return $this->pay_request_error_msg;
    }

    public function getFaPayVerifyErrorAttribute()
    {
        return $this->pay_verify_error_msg;
    }
}
