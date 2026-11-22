<?php


namespace App\Http\Helpers\Classes\PaymentMethod;


use App\Models\Sys\PaymentMethod\SysPaymentMethod;

class Jibimo
{
    private $GuzzleClient;
    private $pay_request_url = 'https://api.jibimo.com/v2/ipg/request';
    private $pay_verify_url = 'https://api.jibimo.com/v2/ipg/verify';
    private $pay_start_base_url = 'https://api.jibimo.com/v2/ipg/redirect/{token}';

    private $api_key = 'd6ee4045-173e-4223-b53a-4e3f85c1e876-764b37b6-149d-41c0-b0ab-c62790846418';
    private $secret_key = 'z7Zsq1qIAw75V2Kv1dog1ixhcnrjBOdmKClXzyRViH2qI7dohtMTY3NjU1MDM2Nw==';
    private $pay_request;

    public $mobile = null;
    public $order_id = null;
    public $description = null;
    public $allowedCards = null;
    public $last_request_result = null;
    public $last_verify_result = null;

    public function __construct($pay_request, $api_key = false)
    {
        $this->pay_request = $pay_request;
        if (!$api_key)
        {
            $this->set_jibimo_setting_from_db();
        }
        else
        {
            $this->api_key = $api_key;
        }

        $this->GuzzleClient = new \GuzzleHttp\Client();
    }


    private function set_jibimo_setting_from_db()
    {
        $jibimo = SysPaymentMethod::jibimo();
        $this->update_pay_request('sys_payment_method_jibimo_id', $jibimo['jibimo_method_setting_id']);
        $this->api_key = $jibimo['api_key'];
        $this->pay_verify_url = $jibimo['pay_verify_url'];
        $this->pay_request_url = $jibimo['pay_request_url'];
        $this->pay_start_base_url = $jibimo['pay_start_base_url'];
    }

    private function pay_request_options()
    {
        $return_url_local = str_replace('http://127.0.0.1:8000', 'https://xdgbit.com', $this->pay_request->call_back_url);
        $options =
            [
                'amount'                  => $this->pay_request->rial_price,
                'return_url'              => $return_url_local,
                'authorized_card_numbers' => [$this->pay_request->authorized_card_number]
            ];

        if ($this->mobile)
        {
            $options['mobile'] = $this->mobile;
        }
        if ($this->order_id)
        {
            $options['orderId'] = $this->order_id;
        }
        if ($this->description)
        {
            $options['description'] = $this->description;
        }
        if ($this->allowedCards)
        {
            $options['allowedCards'] = $this->allowedCards;
        }

        return $options;
    }

    private function update_pay_request($field_name, $value)
    {
        $pay_request = $this->pay_request;
        $pay_request->$field_name = $value;
        $pay_request->save();
        $this->pay_request = $pay_request;
    }


    public function set_amount($amount, $convert_to_rial = true)
    {
        if ($convert_to_rial)
        {
            $amount = $amount * 10;
        }

        $this->amount = $amount;
    }

    public function set_order_id($order_id)
    {
        $this->order_id = $order_id;
    }

    public function set_mobile($mobile)
    {
        $this->mobile = $mobile;
    }

    public function set_description($description)
    {
        $this->description = $description;
    }

    public function set_allowedCards($allowedCards)
    {
        $this->allowedCards = $allowedCards;
    }

    public function set_call_back_url($cull_back_url)
    {
        $this->cull_back_url = $cull_back_url;
    }


//    static function fa_request_pay_result($result_code)
//    {
//        switch ((int)$result_code)
//        {
//            case 100:
//                return 'با موفقیت تایید شد.';
//            case 102:
//                return 'merchant یافت نشد.';
//            case 103:
//                return 'merchant غیرفعال';
//            case 104:
//                return 'merchant نامعتبر';
//            case 105:
//                return 'مبلغ تراکنش بایستی بزرگتر از 1,000 ریال باشد.';
//            case 106:
//                return 'callbackUrl نامعتبر می‌باشد. (شروع با http و یا https)';
//            case 113:
//                return ' مبلغ تراکنش از سقف میزان تراکنش در پرداخت های آنلاین بیشتر است.';
//            case 201:
//                return 'قبلا تایید شده.';
//            default:
//                return false;
//        }
//    }
    static function fa_pay_status($status_code)
    {
        switch ((int)$status_code)
        {
            case -1:
                $res = 'در انتظار پردخت';
                break;
            case -2:
                $res = 'خطای داخلی';
                break;
            case -3:
                $res = 'کاربر در درگاه بانکی روی انصراف کلیک کرده است';
                break;
            case 1:
                $res = 'پرداخت شده - تاییدشده';
                break;
            case 2:
                $res = 'پرداخت شده - تاییدنشده';
                break;
            case 3:
                $res = 'لغوشده توسط کاربر';
                break;
            case 4:
                $res = '‌شماره کارت نامعتبر می‌باشد.';
                break;
            case 5:
                $res = '‌موجودی حساب کافی نمی‌باشد.';
                break;
            case 6:
                $res = 'رمز واردشده اشتباه می‌باشد.';
                break;
            case 7:
                $res = '‌تعداد درخواست‌ها بیش از حد مجاز می‌باشد.';
                break;
            case 8:
                $res = '‌تعداد پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد.';
                break;
            case 9:
                $res = 'مبلغ پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد.';
                break;
            case 10:
                $res = '‌صادرکننده‌ی کارت نامعتبر می‌باشد.';
                break;
            case 11:
                $res = '‌خطای سوییچ';
                break;
            case 12:
                $res = 'کارت قابل دسترسی نمی‌باشد.';
                break;
            default:
                $res = false;
        }

        return $res;
    }

    static function fa_pay_status_callback($status_code)
    {
        switch ((int)$status_code)
        {
            case -3:
                $res = 'مشتری در درگاه بانکی روی انصراف کلیک کرده است';
                break;
            case 99:
                $res = 'پول از حساب مشتری برداشت شده و منتظر verify شدن است';
                break;
            default:
                $res = false;
        }

        return $res;
    }

    static function fa_verify_pay_result($status_code)
    {
        switch ((int)$status_code)
        {
            case 0:
                $res = 'در انتظار پردخت';
                break;
            case -3:
                $res = 'کاربر در درگاه بانکی روی انصراف کلیک کرده است';
                break;
            case -4:
                $res = 'خطا در انجام تراکنش از سمت بانک';
                break;
            case -6:
                $res = 'خطا در انجام عملیات تایید از سمت بانک (چند ثانیه بعد مجددا تلاش کنید)';
                break;
            case -7:
                $res = 'مدت زمان verify تراکنش منقضی شده است(پول به حساب بانکی کاربر عودت خواهد شد)';
                break;
            case 99:
                $res = 'پول از حساب کاربر برداشت شده و منتظر verify شدن است';
                break;
            case 100:
                $res = 'تراکنش با موفقیت تایید شد';
                break;
            case 101:
                $res = 'تراکنش قبلاً تایید شده است';
                break;
            default:
                $res = false;
        }

        return $res;
    }

    public function redirect_pay_url($transaction_id)
    {
        return str_replace('{token}', $transaction_id, $this->pay_start_base_url);
    }


    public function send_pay_request()
    {
        $options = $this->pay_request_options();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-API-KEY'    => $this->api_key
        ];
        $request = $this->GuzzleClient->request(
            'POST',
            $this->pay_request_url,
            [
                'headers'     => $headers,
                'json'        => $options,
                "http_errors" => false
            ]
        );
        $response_pay_request = $request->getBody()->getContents();
        $this->update_pay_request('send_pay_request_receive_data', $response_pay_request);
        $this->last_request_result = $response_pay_request_object = json_decode($response_pay_request);
        if (isset($response_pay_request_object))
        {
            $this->update_pay_request('pay_request_status', 'success');
            $this->update_pay_request('trx', $response_pay_request_object->trx);
            $this->update_pay_request('wage', $response_pay_request_object->wage);
            $this->update_pay_request('original_amount', $response_pay_request_object->original_amount);
            $this->update_pay_request('amount', $response_pay_request_object->amount);
            $this->update_pay_request('redirect_pay_url', self::redirect_pay_url($response_pay_request_object->trx));
        }
        else
        {
            $this->update_pay_request('pay_request_status', 'error');
            $this->update_pay_request('pay_request_error_no', 500);
            $this->update_pay_request('pay_request_error_msg', 'خطا در پردازش اطلاعات.');
        }

        return $this->pay_request;
    }

    public function send_pay_verify()
    {
        $trx = $this->pay_request->trx;
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-API-KEY'    => $this->api_key
        ];
        $options =
            [
                'trx' => $trx
            ];
        $request = $this->GuzzleClient->request(
            'POST',
            $this->pay_verify_url,
            [
                'headers'     => $headers,
                'json'        => $options,
                "http_errors" => false
            ]
        );
        $response_pay_verify = $request->getBody()->getContents();
        $this->update_pay_request('send_pay_verify_send_data', $response_pay_verify);
        $this->last_verify_result = $response_pay_verify_object = json_decode($response_pay_verify);
        if (isset($response_pay_verify_object->status))
        {
            if ($response_pay_verify_object->status . '' === '1')
            {
                if ($response_pay_verify_object->state_code . '' === '100')
                {
                    $this->update_pay_request('pay_verify_status', 'success');
                    $this->update_pay_request('trace_number', $response_pay_verify_object->trace_number);
                    $this->update_pay_request('tracking_id', $response_pay_verify_object->tracking_id);
                    $this->update_pay_request('paid_price', $response_pay_verify_object->original_amount / 10);//original_amount be toman
                    $this->update_pay_request('payment_status_no', $response_pay_verify_object->status);
                    $this->update_pay_request('state_code', $response_pay_verify_object->state_code);
                    $this->update_pay_request('fa_payment_status', self::fa_verify_pay_result($response_pay_verify_object->state_code));
                }
                else
                {
                    $this->update_pay_request('pay_verify_status', 'error');
                    $this->update_pay_request('pay_verify_error_no', $response_pay_verify_object->state_code);
                    $this->update_pay_request('pay_verify_error_msg', self::fa_verify_pay_result($response_pay_verify_object->state_code));
                }

            }
            else
            {
                $this->update_pay_request('pay_verify_status', 'error');
                $this->update_pay_request('pay_verify_error_no', $response_pay_verify_object->state_code);
                $this->update_pay_request('pay_verify_error_msg', self::fa_verify_pay_result($response_pay_verify_object->state_code));
            }
        }
        else
        {
            $this->update_pay_request('pay_verify_status', 'error');
            $this->update_pay_request('pay_verify_error_no', 500);
            $this->update_pay_request('pay_verify_error_msg', 'خطا در پردازش اطلاعات.');
        }


        return $this->pay_request;
    }

    public function calc_api_fee_price($payed_price, $user_customer): array
    {
        $sum_deposit_today = $user_customer->deposit_irr_online_payment()
            ->approve()
            ->where('submitted_at', '>=', \Carbon\Carbon::now()->floorDay())
            ->sum('price');
        if ($sum_deposit_today)
        {
            $sum_deposit_today = intval($sum_deposit_today);
        }
        else
        {
            $sum_deposit_today = 0;
        }
        $payment_method_config = \App\Models\Sys\PaymentMethod\SysOnlinePaymentConfig::query()->notArchive()->last()->first();
        $max_wage_tax = $payment_method_config->max_wage_tax;
        $percent_wage_tax = doubleval($payment_method_config->percent_wage_tax);
        $one_percent_total_payment = intval($payed_price / 100);
        if ($one_percent_total_payment <= 500)
        {
            $fee_payment_gateway = 550;
        }
        elseif ($one_percent_total_payment >= 6000)
        {
            $fee_payment_gateway = 6600;
        }
        else
        {
            $fee_payment_gateway = intval($one_percent_total_payment * 1.1);
        }
        $fee_xdgbit = intval($payed_price * $percent_wage_tax);

        $sum_deposit_today_with_now = $sum_deposit_today + intval($payed_price);

        if ($sum_deposit_today_with_now <= config('constants.price_limited_payment_method') && $fee_xdgbit >= $max_wage_tax)
        {
            $fee_xdgbit = $max_wage_tax;
        }

        $fee_total = $fee_payment_gateway + $fee_xdgbit;

        return [
            'fee_total'  => $fee_total,
            'fee_xdgbit' => $fee_xdgbit,
        ];
    }

}