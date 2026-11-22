<?php

namespace App\Models\Sys\PaymentMethod;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysPaymentMethodVandarRequest extends Model
{
    use SoftDeletes;

    protected $table = 'sys_payment_method_vandar_requests';

    public function getCallBackUrlAttribute()
    {
        return route('callback.online_payment.vandar', ['invoice_tracking_code' => $this->invoice_tracking_code]);
    }

    public function getRialPriceAttribute()
    {
        return $this->price * 10;
    }

    public function getFaPayRequestErrorAttribute()
    {
        $errors = json_decode($this->pay_request_errors);

        return $errors[0];
    }


    public function getFaPayVerifyErrorAttribute()
    {
        $errors = json_decode($this->pay_verify_errors);

        return $errors[0];
    }

}
