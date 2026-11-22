<?php

namespace App\Models\Payment;

use App\Models\User\UserCustomer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Sys\PaymentMethod\SysPaymentMethod;
use App\Models\Sys\PaymentMethod\SysPaymentMethodJibitRequest;
use App\Models\Sys\PaymentMethod\SysPaymentMethodZibalRequest;
use App\Models\Sys\PaymentMethod\SysPaymentMethodJibimoRequest;
use App\Models\Sys\PaymentMethod\SysPaymentMethodVandarRequest;

class OnlinePaymentInvoice extends Model
{
    protected $table = 'online_payment_invoices';

    protected $dates = ['expired_at'];

    use SoftDeletes;


    private static function generate_invoice_number()
    {
        $invoice_in_day_count = self::whereBetween('created_at', [date('Y-m-d') . ' 00:00:00', date('Y-m-d') . ' 23:59:59'])->count();

        return '20_' . JDate('Ymd', 'en') . '_' . sprintf("%04d", ++$invoice_in_day_count + 1000);
    }

    private static function generate_invoice_tracking()
    {
        $tracking_code = substr(strtolower(md5(rand(1, 1000000))), rand(1, 7), 20);
        $exist_tracking_code = self::where('invoice_tracking_code', $tracking_code)->first();
        if ($exist_tracking_code)
        {
            return self::generate_invoice_tracking();
        }

        return $tracking_code;
    }

    public function setComputedFields()
    {
        $this->invoice_number = self::generate_invoice_number();
        $this->invoice_tracking_code = self::generate_invoice_tracking();
        $this->expired_at = Carbon::now()->addMinutes(20);
        $this->submitted_at = Carbon::now();
    }

    public function user_customer()
    {
        return $this->belongsTo(UserCustomer::class, 'user_customer_id', 'id');
    }

    public function payment_method()
    {
        return $this->belongsTo(SysPaymentMethod::class, 'payment_method_id', 'id');
    }

    public function user_customer_bank_account()
    {
        return $this->belongsTo(UserCustomerBankAccount::class, 'user_customer_bank_account_id', 'id');
    }

    public function payment_method_zibal_request()
    {
        return $this->belongsTo(SysPaymentMethodZibalRequest::class, 'payment_method_request_id', 'id');
    }

    public function payment_method_vandar_request()
    {
        return $this->belongsTo(SysPaymentMethodVandarRequest::class, 'payment_method_request_id', 'id');
    }

    public function payment_method_jibit_request()
    {
        return $this->belongsTo(SysPaymentMethodJibitRequest::class, 'payment_method_request_id', 'id');
    }

    public function payment_method_jibimo_request()
    {
        return $this->belongsTo(SysPaymentMethodJibimoRequest::class, 'payment_method_request_id', 'id');
    }


    public function scopeExpired($query)
    {
        return $query->whereDate('expired_at', '<=', Carbon::now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApprove($query)
    {
        return $query->where('status', 'approve');
    }


    public function payRequest()
    {
        $active_payment_method = SysPaymentMethod::get_active_method();
        $this->payment_method_id = $active_payment_method->id;
        $this->save();

        $pay_request = $active_payment_method->pay($this);
        $this->payment_method_request_id = $pay_request->id;
        $this->save();

        return $pay_request;
    }

    public function getPaymentTitleAttribute()
    {
        $payment_method = $this->payment_method;
        if ($payment_method)
        {
            return $payment_method->title;
        }
        else
        {
            return null;
        }
    }

    public function getPaymentTitleDevTitleAttribute()
    {
        return $this->payment_method->dev_title;
    }

    public function getFaStatusAttribute()
    {
        switch ($this->status)
        {
            case 'pending':
                if ($this->expired_at->greaterThan(Carbon::now()))
                {
                    return 'در انتظار پرداخت';
                }
                else
                {
                    return 'منقضی شده';
                }
            case 'approve':
                return 'پرداخت شده';
            case 'failed':
                return 'ناموفق';
            default:
                return '_!!!_';
        }
    }

    public function getFaPriceAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->price));

    }

    public function getUserCustomerInfoAttribute()
    {
        return $this->user_customer->setAppends(['full_name', 'fa_xdgbit_customer_id', 'avatar_src']);

    }

    public function getUserCustomerMinInfoAttribute()
    {
        return $this->user_customer->min_info;
    }


    public function getFaInvoiceNumberAttribute()
    {
        return ConvertNumbersEntoFa($this->invoice_number);
    }

    public function getJalaliExpiredAtAttribute()
    {
        return $this->expired_at ? ConvertNumbersEntoFa(verta($this->expired_at) . '') : null;
    }


    public function getJalaliStatusChangedAtAttribute()
    {
        return $this->status_changed_at ? ConvertNumbersEntoFa(verta($this->status_changed_at) . '') : null;
    }

    public function getJalaliSubmittedAtAttribute()
    {
        return $this->submitted_at ? ConvertNumbersEntoFa(verta($this->submitted_at) . '') : null;
    }

    public function getAvatarSrcUserCustomerAttribute()
    {
        return $this->user_customer ? $this->user_customer->AvatarSrc : '';
    }

    public function getFullNameUserCustomerAttribute()
    {
        return $this->user_customer ? $this->user_customer->full_name : '';
    }

    public function getXdgbitCustomerIdAttribute()
    {
        return $this->user_customer ? $this->user_customer->xdgbit_customer_id : '';
    }

    public function getEmailUserCustomerAttribute()
    {
        return $this->user_customer ? $this->user_customer->email : '';
    }

    public function getFaUserCustomerBankAccountCardNumberAttribute()
    {
        return ConvertNumbersEntoFa($this->user_customer_bank_account_card_number);
    }

    public function getInvoiceStatusAlphaClassAttribute()
    {
        switch ($this->status)
        {
            case 'pending':
                return 'alpha-purple';
                break;
            case 'failed':
                return 'alpha-danger';
                break;
            case 'approve':
                return 'alpha-teal';
                break;
            default:
                return 'alpha-grey';
                break;
        }
    }

}
