<?php


namespace App\Http\Helpers\Classes\PaymentMethod;


use App\Models\Sys\PaymentMethod\SysPaymentMethod;

class Zibal
{
    private $GuzzleClient;
    private $merchant = '';
    private $pay_request_url = 'https://gateway.zibal.ir/v1/request';
    private $pay_verify_url = 'https://gateway.zibal.ir/v1/verify';
    private $pay_request;

    public $mobile = null;
    public $order_id = null;
    public $description = null;
    public $allowedCards = null;
    public $last_request_result = null;
    public $last_verify_result = null;

    public function __construct($pay_request, $merchant = false)
    {
        $this->pay_request = $pay_request;

        if (!$merchant)
        {
            $this->set_zibal_setting_from_db();
        }
        else
        {
            $this->merchant = $merchant;
        }

        $this->GuzzleClient = new \GuzzleHttp\Client();
    }


    private function set_zibal_setting_from_db()
    {
        $zibal = SysPaymentMethod::zibal();

        $this->update_pay_request('sys_payment_method_zibal_id', $zibal['zibal_method_setting_id']);

        $this->merchant = $zibal['merchant'];
        $this->pay_verify_url = $zibal['pay_verify_url'];
        $this->pay_request_url = $zibal['pay_request_url'];
    }

    private function pay_request_options()
    {
        $options =
            [
                'merchant'    => $this->merchant,
                'amount'      => $this->pay_request->rial_price,
                'callbackUrl' => $this->pay_request->call_back_url,
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


    static function fa_request_pay_result($result_code)
    {
        switch ((int)$result_code)
        {
            case 100:
                return 'با موفقیت تایید شد.';
            case 102:
                return 'merchant یافت نشد.';
            case 103:
                return 'merchant غیرفعال';
            case 104:
                return 'merchant نامعتبر';
            case 105:
                return 'مبلغ تراکنش بایستی بزرگتر از 1,000 ریال باشد.';
            case 106:
                return 'callbackUrl نامعتبر می‌باشد. (شروع با http و یا https)';
            case 113:
                return ' مبلغ تراکنش از سقف میزان تراکنش در پرداخت های آنلاین بیشتر است.';
            case 201:
                return 'قبلا تایید شده.';
            default:
                return false;
        }
    }

    static function fa_verify_pay_result($result_code)
    {
        switch ((int)$result_code)
        {
            case 100:
                return 'با موفقیت تایید شد.';
            case 102:
                return 'merchant یافت نشد.';
            case 103:
                return 'merchant غیرفعال';
            case 104:
                return 'merchant نامعتبر';
            case 201:
                return 'قبلا تایید شده.';
            case 202:
                return 'سفارش پرداخت نشده یا ناموفق بوده است.';
            case 203:
                return 'trackId نامعتبر می‌باشد.';
            default:
                return false;
        }
    }

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

    static function redirect_pay_url($transaction_id)
    {
        return 'https://gateway.zibal.ir/start/' . $transaction_id;
    }


    public function send_pay_request()
    {
        $options = $this->pay_request_options();

        $this->update_pay_request('send_pay_request_send_data', json_encode($options));

        $request = $this->GuzzleClient->request(
            'POST',
            $this->pay_request_url,
            [
                'json'        => $options,
                "http_errors" => false
            ]
        );

        $response_pay_request = $request->getBody()->getContents();

        $this->update_pay_request('send_pay_request_receive_data', $response_pay_request);

        $this->last_request_result = $response_pay_request_object = json_decode($response_pay_request);

        if (isset($response_pay_request_object->result))
        {
            if ($response_pay_request_object->result . '' === '100')
            {
                $this->update_pay_request('pay_request_status', 'success');
                $this->update_pay_request('track_id', $response_pay_request_object->trackId);
                $this->update_pay_request('redirect_pay_url', self::redirect_pay_url($response_pay_request_object->trackId));
            }
            else
            {
                $this->update_pay_request('pay_request_status', 'error');
                $this->update_pay_request('pay_request_error_no', $response_pay_request_object->result);
                $this->update_pay_request('pay_request_error_msg', self::fa_request_pay_message($response_pay_request_object->result));
            }
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
        $transaction_id = $this->pay_request->track_id;

        $options =
            [
                'merchant' => $this->merchant,
                'trackId'  => $transaction_id
            ];

        $this->update_pay_request('send_pay_verify_send_data', json_encode($options));

        $request = $this->GuzzleClient->request(
            'POST',
            $this->pay_verify_url,
            [
                'json'        => $options,
                "http_errors" => false
            ]
        );

        $response_pay_verify = $request->getBody()->getContents();

        $this->update_pay_request('send_pay_verify_send_data', $response_pay_verify);

        $this->last_verify_result = $response_pay_verify_object = json_decode($response_pay_verify);

        if (isset($response_pay_verify_object->result))
        {
            if ($response_pay_verify_object->result . '' === '100')
            {

                $this->update_pay_request('pay_verify_status', 'success');
                $this->update_pay_request('paid_price', $response_pay_verify_object->amount / 10);
                $this->update_pay_request('payment_status_no', $response_pay_verify_object->status);
                $this->update_pay_request('fa_payment_status', self::fa_pay_status($response_pay_verify_object->status));
            }
            else
            {
                $this->update_pay_request('pay_verify_status', 'error');
                $this->update_pay_request('pay_verify_error_no', $response_pay_verify_object->result);
                $this->update_pay_request('pay_verify_error_msg', self::fa_verify_pay_result($response_pay_verify_object->result));
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
}