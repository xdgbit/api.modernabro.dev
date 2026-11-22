<?php

namespace App\Models\Sys\PaymentMethod;

use App\Http\Helpers\Classes\PaymentMethod\Jibimo;
use App\Http\Helpers\Classes\PaymentMethod\Zibal;
use App\Http\Helpers\Classes\PaymentMethod\Vandar;
use App\Http\Helpers\Classes\PaymentMethod\Jibit\Jibit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysPaymentMethod extends Model
{
    use SoftDeletes;

    protected $table = 'sys_payment_method';

    public static function zibal()
    {
        $zibal = self::where('dev_title', 'zibal_ir')->first();
        if ($zibal)
        {
            $zibal_method_setting = $zibal->sys_payment_method_zibals()->notArchive()->last()->first();
            if ($zibal_method_setting)
            {
                return
                    [
                        'merchant'                => $zibal_method_setting->merchant,
                        'pay_request_url'         => $zibal_method_setting->pay_request_url,
                        'pay_verify_url'          => $zibal_method_setting->pay_verify_url,
                        'zibal_method_setting_id' => $zibal_method_setting->id
                    ];
            }
        }

        return
            [
                'merchant'                => config('payment_zibal.merchant'),
                'pay_verify_url'          => config('payment_zibal.pay_verify_url', 'https://gateway.zibal.ir/v1/verify'),
                'pay_request_url'         => config('payment_zibal.pay_request_url', 'https://gateway.zibal.ir/v1/request'),
                'zibal_method_setting_id' => -1
            ];
    }

    public static function vandar()
    {
        $vandar = self::where('dev_title', 'vandar_io')->first();
        if ($vandar)
        {
            $vandar_method_setting = $vandar->sys_payment_method_vandars()->notArchive()->last()->first();
            if ($vandar_method_setting)
            {
                return
                    [
                        'api_key'                  => $vandar_method_setting->api_key,
                        'pay_2step_url'            => $vandar_method_setting->pay_2step_url,
                        'pay_verify_url'           => $vandar_method_setting->pay_verify_url,
                        'pay_request_url'          => $vandar_method_setting->pay_request_url,
                        'pay_start_base_url'       => $vandar_method_setting->pay_start_base_url,
                        'vandar_method_setting_id' => $vandar_method_setting->id,

                    ];
            }
        }

        return
            [
                'api_key'                  => config('payment_vandar.api_key', 'b44fbd8273bd9988900a64c7b969593b5d004046'),
                'pay_2step_url'            => config('payment_vandar.pay_2step_url', 'https://vandar.io/api/ipg/2step/transaction'),
                'pay_verify_url'           => config('payment_vandar.pay_verify_url', 'https://ipg.vandar.io/api/v3/verify'),
                'pay_request_url'          => config('payment_vandar.pay_request_url', 'https://ipg.vandar.io/api/v3/send'),
                'pay_start_base_url'       => config('payment_vandar.pay_start_base_url', 'https://ipg.vandar.io/v3/{token}'),
                'vandar_method_setting_id' => -1
            ];
    }

    public static function jibit()
    {
        $jibit = self::where('dev_title', 'jibit_ir')->first();
        if ($jibit)
        {
            $jibit_method_setting = $jibit->sys_payment_method_jibits()->notArchive()->last()->first();
            if ($jibit_method_setting)
            {
                return
                    [
                        'jibit_method_setting_id' => $jibit_method_setting->id,
                        'trf_url'                 => $jibit_method_setting->trf_url,
                        'ide_url'                 => $jibit_method_setting->ide_url,
                        'base_url'                => $jibit_method_setting->base_url,
                        'api_key'                 => $jibit_method_setting->api_key,
                        'secret_key'              => $jibit_method_setting->secret_key,
                    ];
            }
        }

        return
            [
                'api_key'                 => config('payment_jibit.api_key'),//api_key,
                'secret_key'              => config('payment_jibit.secret_key'),//secret_key,
                'base_url'                => config('payment_jibit.base_url'),//base_url,
                'jibit_method_setting_id' => -1,//id
            ];
    }

    public static function jibimo()
    {
        $jibimo = self::where('dev_title', 'jibimo_com')->first();
        if ($jibimo)
        {
            $jibimo_method_setting = $jibimo->sys_payment_method_jibimos()->notArchive()->last()->first();
            if ($jibimo_method_setting)
            {
                return
                    [
                        'api_key'                  => $jibimo_method_setting->api_key,
                        'user_name'                => $jibimo_method_setting->user_name,
                        'password'                 => $jibimo_method_setting->password,
                        'secret_key'               => $jibimo_method_setting->secret_key,
                        'pay_start_base_url'       => $jibimo_method_setting->pay_start_base_url,
                        'pay_request_url'          => $jibimo_method_setting->pay_request_url,
                        'pay_verify_url'           => $jibimo_method_setting->pay_verify_url,
                        'jibimo_method_setting_id' => $jibimo_method_setting->id
                    ];
            }
        }

        return
            [
                'merchant'                 => config('payment_zibal.merchant'),
                'pay_verify_url'           => config('payment_zibal.pay_verify_url', 'https://gateway.zibal.ir/v1/verify'),
                'pay_request_url'          => config('payment_zibal.pay_request_url', 'https://gateway.zibal.ir/v1/request'),
                'jibimo_method_setting_id' => -1
            ];
    }

    public function sys_payment_method_customize()
    {
        return $this->hasOne(SysPaymentMethodCustomize::class, 'sys_payment_method_id', 'id');
    }

    public static function get_active_method()
    {
        $last_active_record = \App\Models\Sys\PaymentMethod\SysPaymentMethodDefault::notArchive()->last()->first();

        if ($last_active_record)
        {
            return $last_active_record->sys_payment_method;
        }
        else
        {
            return null;
        }
    }

//    public static function get_active_method()
//    {
//        $last_active_record = \App\Models\Sys\PaymentMethod\SysPaymentMethodDefault::notArchive()->last()->first();
//
//        if ($last_active_record)
//        {
//            return $last_active_record;
//        }
//        else
//        {
//            return null;
//        }
//    }
//    public static function get_customize_status()
//    {
//        $last_active_record = \App\Models\Sys\PaymentMethod\SysPaymentMethodDefault::notArchive()->last()->first();
//
//        if ($last_active_record)
//        {
//            return $last_active_record->customize_status;
//        }
//        else
//        {
//            return null;
//        }
//    }

    //=============================== Relations =============================//

    public function sys_payment_method_zibals()
    {
        return $this->hasMany(\App\Models\Sys\PaymentMethod\SysPaymentMethodZibal::class, 'sys_payment_method_id', 'id');
    }

    public function sys_payment_method_vandars()
    {
        return $this->hasMany(\App\Models\Sys\PaymentMethod\SysPaymentMethodVandar::class, 'sys_payment_method_id', 'id');
    }

    public function sys_payment_method_jibits()
    {
        return $this->hasMany(\App\Models\Sys\PaymentMethod\SysPaymentMethodJibit::class, 'sys_payment_method_id', 'id');
    }

    public function sys_payment_method_jibimos()
    {
        return $this->hasMany(\App\Models\Sys\PaymentMethod\SysPaymentMethodJibimo::class, 'sys_payment_method_id', 'id');
    }


    //===========================================================//

    private function new_zibal_pay_request($price, $invoice_tracking_cod)
    {
        $new_vandar_request = new SysPaymentMethodZibalRequest();
        $new_vandar_request->price = $price;
        $new_vandar_request->invoice_tracking_code = $invoice_tracking_cod;
        $new_vandar_request->save();

        return $new_vandar_request;
    }

    private function zibal_payment($invoice)
    {
        $pay_request = $this->new_zibal_pay_request($invoice->price, $invoice->invoice_tracking_code);

        if ($this->is_active === '1')
        {
            $user_customer = $invoice->user_customer;
            $user_customer_bank_account = $invoice->user_customer_bank_account;

            $zibalPayment = new Zibal($pay_request);
            $zibalPayment->set_order_id($invoice->invoice_number);
            $zibalPayment->set_mobile($user_customer->approved_mobile_number);
            $zibalPayment->set_allowedCards([$user_customer_bank_account->card_number]);

            return $zibalPayment->send_pay_request();
        }
        else
        {
            $pay_request->pay_request_status = 'error';
            $pay_request->pay_request_error_no = 503;
            $pay_request->pay_request_error_msg = 'وضعیت درگاه غیر فعال می باشد.';
            $pay_request->save();

            return $pay_request;
        }
    }

    //================================================================//

    private function new_vandar_pay_request($price, $invoice_tracking_cod)
    {
        $new_vandar_request = new SysPaymentMethodVandarRequest();
        $new_vandar_request->price = $price;
        $new_vandar_request->invoice_tracking_code = $invoice_tracking_cod;
        $new_vandar_request->save();

        return $new_vandar_request;
    }

    private function vandar_payment($invoice)
    {
        $pay_request = $this->new_vandar_pay_request($invoice->price, $invoice->invoice_tracking_code);
        if ($this->is_active === '1')
        {
            $user_customer = $invoice->user_customer;
            $user_customer_bank_account = $invoice->user_customer_bank_account;

            $vandarPayment = new Vandar($pay_request);
            $vandarPayment->set_factor_number($invoice->invoice_number);
            $vandarPayment->set_mobile_number($user_customer->approved_mobile_number);
            $vandarPayment->set_valid_card_number($user_customer_bank_account->card_number);

            return $vandarPayment->send_pay_request();
        }
        else
        {
            $pay_request->pay_request_status = 'error';
            $pay_request->pay_request_errors = ['وضعیت درگاه غیر فعال می باشد.'];
            $pay_request->save();

            return $pay_request;
        }
    }

    //================================================================//

    private function new_jibimo_pay_request($price, $invoice)
    {
        $new_jibimo_request = new SysPaymentMethodJibimoRequest();
        $new_jibimo_request->price = $price;
        $new_jibimo_request->invoice_tracking_code = $invoice->invoice_tracking_code;
        $new_jibimo_request->authorized_card_number = $invoice->user_customer_bank_account_card_number;
        $new_jibimo_request->save();

        return $new_jibimo_request;
    }

    private function jibimo_payment($invoice)
    {
        $pay_request = $this->new_jibimo_pay_request($invoice->price, $invoice);
        if ($this->is_active === '1')
        {
            $user_customer = $invoice->user_customer;
            $user_customer_bank_account = $invoice->user_customer_bank_account;
            $jibimoPayment = new Jibimo($pay_request);
            $jibimoPayment->set_order_id($invoice->invoice_number);
            $jibimoPayment->set_mobile($user_customer->approved_mobile_number);
            $jibimoPayment->set_allowedCards([$user_customer_bank_account->card_number]);

            return $jibimoPayment->send_pay_request();
        }
        else
        {
            $pay_request->pay_request_status = 'error';
            $pay_request->pay_request_errors = ['وضعیت درگاه غیر فعال می باشد.'];
            $pay_request->save();

            return $pay_request;
        }
    }

    //================================================================//

    private function new_jibit_pay_request($price, $invoice_tracking_cod)
    {
        $new_jibit_request = new SysPaymentMethodJibitRequest();
        $new_jibit_request->price = $price;
        $new_jibit_request->invoice_tracking_code = $invoice_tracking_cod;
        $new_jibit_request->save();

        return $new_jibit_request;
    }

    private function jibit_payment($invoice)
    {
        $pay_request = $this->new_jibit_pay_request($invoice->price, $invoice->invoice_tracking_code);
        if ($this->is_active === '1')
        {
            $user_customer = $invoice->user_customer;
            $user_customer_bank_account = $invoice->user_customer_bank_account;

            $jibitPayment = new Jibit($pay_request);
            $jibitPayment->set_client_reference_number($invoice->invoice_number);
            $jibitPayment->set_user_identifier($user_customer->xdgbit_customer_id);
            $jibitPayment->set_payer_card_number($user_customer_bank_account->card_number);

            return $jibitPayment->send_pay_request();
        }
        else
        {
            $pay_request->pay_request_status = 'error';
            $pay_request->pay_request_error_no = -100;
            $pay_request->pay_request_error_msg = 'وضعیت درگاه غیر فعال می باشد.';
            $pay_request->save();

            return $pay_request;
        }
    }


    //================================================================//
    public function pay($invoice)
    {
        $dev_title = $this->dev_title;
        switch ($dev_title)
        {
            case 'zibal_ir':
                return $this->zibal_payment($invoice);
            case 'vandar_io':
                return $this->vandar_payment($invoice);
            case 'jibit_ir':
                return $this->jibit_payment($invoice);
            case 'jibimo_com':
                return $this->jibimo_payment($invoice);
            default :
                return false;
        }
    }

    //====================================================//

    public function getSysPaymentMethodVandarAttribute()
    {
        $sys_payment = \App\Models\Sys\PaymentMethod\SysPaymentMethodVandar::NotArchive()->Last()->first();

        if ($sys_payment)
        {
            $sys_payment->setAppends(['editor_full_name', 'jalali_edited_at']);
        }

        return $sys_payment;
    }

    public function getSysPaymentMethodJibimoAttribute()
    {
        $sys_payment = \App\Models\Sys\PaymentMethod\SysPaymentMethodJibimo::NotArchive()->Last()->first();

        if ($sys_payment)
        {
            $sys_payment->setAppends(['editor_full_name', 'jalali_edited_at']);
        }

        return $sys_payment;
    }

    public function getSysPaymentMethodZibalAttribute()
    {
        $sys_payment = \App\Models\Sys\PaymentMethod\SysPaymentMethodZibal::NotArchive()->Last()->first();
        if ($sys_payment)
        {
            $sys_payment->setAppends(['editor_full_name', 'jalali_edited_at']);
        }

        return $sys_payment;
    }

    public function getSysPaymentMethodJibitAttribute()
    {
        $sys_payment = \App\Models\Sys\PaymentMethod\SysPaymentMethodJibit::NotArchive()->Last()->first();
        if ($sys_payment)
        {
            $sys_payment->setAppends(['editor_full_name', 'jalali_edited_at']);
        }

        return $sys_payment;
    }

    public function getStatusBgClassAttribute()
    {
        switch ($this->is_active)
        {
            case '1':
                return 'bg-teal-700 text-light text-shadow-dark';
            case '0':
                return 'bg-danger-700 text-light text-shadow-dark';
            default:
                return 'alpha-grey text-dark text-shadow-light';
        }
    }

    public function getStatusAlphaClassAttribute()
    {
        switch ($this->is_active)
        {
            case '1':
                return 'alpha-teal';
            case '0':
                return 'alpha-danger';
            default:
                return 'alpha-grey';
        }
    }

    public function getStatusBorderClassAttribute()
    {
        switch ($this->is_active)
        {
            case '1':
                return 'border-teal-800';
            case '0':
                return 'border-danger-800';
            default:
                return 'border-grey-300';
        }
    }

    public function getStatusTextClassAttribute()
    {
        switch ($this->is_active)
        {
            case '1':
                return 'text-success-800';
            case '0':
            default:
                return 'text-muted';
        }
    }

    public function getStatusTextAttribute()
    {
        switch ($this->is_active)
        {
            case '1':
                return 'فعال';
            case '0':
                return 'غیر فعال';
            default:
                return 'نامشخص';
        }
    }

    public function getLastTransferConfigVandarAttribute()
    {
        $transfer_irr_config = SysTransferIRRConfig::query()->where('dev_title_payment', 'vandar_io')->notArchive()->last()->first();

        return $transfer_irr_config;
    }

    public function getLastActivePaymentMethodCustomizeAttribute()
    {
        $last_active_payment_method_customize = $this->sys_payment_method_customize()->notArchive()->last()->first();
        if ($last_active_payment_method_customize)
        {
//            $last_active_payment_method_customize->setAppends(
//                [
//
//                ]
//            );

            return $last_active_payment_method_customize;
        }
        else
        {
            return null;
        }
    }

    public function getLastTransferConfigJibitAttribute()
    {
        $transfer_irr_config = SysTransferIRRConfig::query()->where('dev_title_payment', 'jibit_ir')->notArchive()->last()->first();

        return $transfer_irr_config;
    }

    public function getLastTransferConfigJibimoAttribute()
    {
        $transfer_irr_config = SysTransferIRRConfig::query()->where('dev_title_payment', 'jibimo_com')->notArchive()->last()->first();

        return $transfer_irr_config;
    }

    public function getLastTransferConfigZibalAttribute()
    {
        $transfer_irr_config = SysTransferIRRConfig::query()->where('dev_title_payment', 'zibal_ir')->notArchive()->last()->first();

        return $transfer_irr_config;
    }

    public function getFaTitleAttribute()
    {
        switch ($this->dev_title)
        {
            case 'vandar_io':
                return 'وندار';
            case 'jibit_ir':
                return 'جیبیت';
            case 'jibimo_com':
                return 'جیبیمو';
            case 'zibal_ir':
                return 'زیبال';
            default:
                return '__!!!!__';
        }
    }
}
