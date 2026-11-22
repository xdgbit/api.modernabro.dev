<?php


namespace App\Http\Helpers\Classes\PaymentMethod;


use App\Models\Sys\PaymentMethod\SysPaymentMethod;

class Vandar
{
    private $pay_request;
    private $GuzzleClient;
    private $api_key = '';
    private $pay_2step_url = 'https://vandar.io/api/ipg/2step/transaction';
    private $pay_verify_url = 'https://ipg.vandar.io/api/v3/verify';
    private $pay_request_url = 'https://ipg.vandar.io/api/v3/send';
    private $pay_start_base_url = 'https://ipg.vandar.io/v3/{token}';

    public $mobile_number = null;
    public $factor_number = null;
    public $national_code = null;
    public $description = null;
    public $valid_card_number = null;
    public $last_request_result = null;
    public $last_2step_result = null;
    public $last_verify_result = null;

    public function __construct($pay_request, $api_key = false)
    {

        $this->pay_request = $pay_request;

        if (!$api_key)
        {
            $this->set_vandar_setting_from_db();
        }
        else
        {
            $this->api_key = $api_key;
        }

        $this->GuzzleClient = new \GuzzleHttp\Client();
    }

    private function set_vandar_setting_from_db()
    {
        $vandar = SysPaymentMethod::vandar();

        $this->update_pay_request('sys_payment_method_vandar_id', $vandar['vandar_method_setting_id']);

        $this->api_key = $vandar['api_key'];
        $this->pay_2step_url = $vandar['pay_2step_url'];
        $this->pay_verify_url = $vandar['pay_verify_url'];
        $this->pay_request_url = $vandar['pay_request_url'];
        $this->pay_start_base_url = $vandar['pay_start_base_url'];
    }

    private function pay_request_options()
    {
        $options =
            [
                'api_key'      => $this->api_key,
                'amount'       => $this->pay_request->rial_price,
//                'callback_url' => $this->pay_request->call_back_url,
                'callback_url' => str_replace('com','net',$this->pay_request->call_back_url),
            ];

        if ($this->mobile_number)
        {
            $options['mobile_number'] = $this->mobile_number;
        }

        if ($this->factor_number)
        {
            $options['factorNumber'] = $this->factor_number;
        }

        if ($this->national_code)
        {
            $options['national_code'] = $this->national_code;
        }

        if ($this->description)
        {
            $options['description'] = $this->description;
        }

        if ($this->valid_card_number)
        {
            $options['valid_card_number'] = $this->valid_card_number;
        }

        return $options;
    }


    public function set_factor_number($factor_number)
    {
        $this->factor_number = $factor_number;
    }

    public function set_mobile_number($mobile_number)
    {
        $this->mobile_number = $mobile_number;
    }

    public function set_description($description)
    {
        $this->description = $description;
    }

    public function set_valid_card_number($valid_card_number)
    {
        $this->valid_card_number = $valid_card_number;
    }

    public function set_call_back_url($cull_back_url)
    {
        $this->cull_back_url = $cull_back_url;
    }

    public function set_national_code($national_code)
    {
        $this->national_code = $national_code;
    }

    private function update_pay_request($field_name, $value)
    {
        $pay_request = $this->pay_request;
        $pay_request->$field_name = $value;
        $pay_request->save();

        $this->pay_request = $pay_request;
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

        if (isset($response_pay_request_object->status))
        {
            if ($response_pay_request_object->status === 1)
            {
                $this->update_pay_request('pay_request_status', 'success');
                $this->update_pay_request('token', $response_pay_request_object->token);
                $this->update_pay_request('redirect_pay_url', $this->redirect_pay_url($response_pay_request_object->token));
            }
            else
            {
                $this->update_pay_request('pay_request_status', 'error');
                $this->update_pay_request('pay_request_errors', json_encode($response_pay_request_object->errors));
            }
        }
        else
        {
            $this->update_pay_request('pay_request_status', 'error');
            $this->update_pay_request('pay_request_errors', json_encode(['خطا در پردازش اطلاعات.']));
        }

        return $this->pay_request;
    }

    public function redirect_pay_url($transaction_id)
    {
        return str_replace('{token}', $transaction_id, $this->pay_start_base_url);
    }

    public function send_pay_2step()
    {
        $transaction_id = $this->pay_request->token;

        $options =
            [
                'api_key' => $this->api_key,
                'token'   => $transaction_id
            ];

        $this->update_pay_request('send_pay_2step_send_data', json_encode($options));

        $request = $this->GuzzleClient->request(
            'POST',
            $this->pay_2step_url,
            [
                'json'        => $options,
                "http_errors" => false
            ]
        );

        $response_2step = $request->getBody()->getContents();

        $this->update_pay_request('send_pay_2step_receive_data', $response_2step);

        $this->last_2step_result = $response_2step_object = json_decode($response_2step);

        if (isset($response_2step_object->status))
        {
            if ($response_2step_object->status === 1)
            {
                $this->update_pay_request('pay_2step_status', 'success');
                $this->update_pay_request('paid_price', (int)$response_2step_object->amount / 10);
                $this->update_pay_request('wage', $response_2step_object->wage / 10);
                //$this->update_pay_request('real_amount', $response_2step_object->real_amount / 10);
                $this->update_pay_request('tracking_code', $response_2step_object->trackingCode);
                $this->update_pay_request('used_cid_sha256_hash', $response_2step_object->cid);
            }
            else
            {
                $this->update_pay_request('pay_2step_status', 'error');
                $this->update_pay_request('pay_2step_errors', json_encode($response_2step_object->errors));
            }
        }
        else
        {
            $this->update_pay_request('pay_2step_status', 'error');
            $this->update_pay_request('pay_2step_errors', json_encode(['خطا در پردازش اطلاعات.']));
        }

        return $this->pay_request;
    }

    public function send_pay_verify()
    {
        $transaction_id = $this->pay_request->token;

        $options =
            [
                'api_key' => $this->api_key,
                'token'   => $transaction_id
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

        $response_verify = $request->getBody()->getContents();

        $this->update_pay_request('send_pay_verify_receive_data', $response_verify);

        $this->last_verify_result = $response_verify_object = json_decode($response_verify);

        if (isset($response_verify_object->status))
        {
            if ($response_verify_object->status === 1)
            {
                $this->update_pay_request('pay_verify_status', 'success');
                $this->update_pay_request('wage', $response_verify_object->wage);
            }
            else
            {
                $this->update_pay_request('pay_verify_status', 'error');
                $this->update_pay_request('pay_verify_errors', json_encode($response_verify_object->errors));
            }
        }
        else
        {
            $this->update_pay_request('pay_verify_status', 'error');
            $this->update_pay_request('pay_verify_errors', json_encode(['خطا در پردازش اطلاعات.']));
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