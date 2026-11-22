<?php

namespace App\Http\Helpers\Classes\PaymentMethod\Jibit;

use App\Models\Sys\PaymentMethod\SysPaymentMethod;

class Jibit
{
    private $pay_request;
    private $currency = 'IRR';
    private $description = null;
    private $additional_data = null;
    private $user_identifier = null;
    private $payer_card_number = null;
    private $payer_national_code = null;
    private $client_reference_number = null;


    // private $cull_back_url = null;

    private $cache;
    private $apiKey = '';
    private $secretKey = '';
    private $accessToken;
    private $refreshToken;
    private $base_url = 'https://napi.jibit.ir/ppg/v3';

    public function __construct($pay_request, $api_data = [])
    {
        $this->pay_request = $pay_request;
        $this->cache = new Cache('jibit_payment');
        if (isset($api_data['api_key']) && isset($api_data['secret_key']))
        {
            $this->apiKey = $api_data['api_key'];
            $this->secretKey = $api_data['secret_key'];
        }
        else
        {
            $this->set_jibit_setting_from_db();
        }
    }

    private function set_jibit_setting_from_db($force_update_pay_request = true)
    {
        $jibit = SysPaymentMethod::jibit();

        $this->apiKey = $jibit['api_key'];
        $this->secretKey = $jibit['secret_key'];

        $this->base_url = $jibit['base_url'];

        if ($force_update_pay_request)
        {
            $this->update_pay_request('sys_payment_method_jibit_id', $jibit['jibit_method_setting_id']);
        }
    }

    private function update_pay_request($field_name, $value)
    {
        $pay_request = $this->pay_request;
        $pay_request->$field_name = $value;
        $pay_request->save();

        $this->pay_request = $pay_request;
    }

    private function pay_request_options(): array
    {
        return
            [
                'apiKey'      => $this->apiKey,
                'accessToken' => $this->getAccessToken(),

                'amount'                => $this->pay_request->rial_price,
                'currency'              => $this->currency,
                'callbackUrl'           => $this->pay_request->call_back_url,
                'userIdentifier'        => $this->user_identifier,
                'payerCardNumber'       => $this->payer_card_number,
                'payerNationalCode'     => $this->payer_national_code,
                'clientReferenceNumber' => $this->client_reference_number,

                'description'    => $this->description,
                'additionalData' => $this->additional_data,
            ];
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
        $online_config = \App\Models\Sys\PaymentMethod\SysOnlinePaymentConfig::query()->notArchive()->last()->first();
        $max_wage_tax = $online_config->max_wage_tax;
        $limit_max_price = $online_config->limit_max_price;
        $percent_wage_tax = doubleval($online_config->percent_wage_tax);
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

        if ($sum_deposit_today_with_now <= $limit_max_price && $fee_xdgbit >= $max_wage_tax)
        {
            $fee_xdgbit = $max_wage_tax;
        }

        $fee_total = $fee_payment_gateway + $fee_xdgbit;

        return [
            'fee_total'  => $fee_total,
            'fee_xdgbit' => $fee_xdgbit,
        ];
    }


    //====================================== public set functions ===============================//


    public function set_user_identifier($user_identifier)
    {
        $this->user_identifier = $user_identifier;
    }

    public function set_payer_card_number($payer_card_number)
    {
        $this->payer_card_number = $payer_card_number;
    }

    public function set_payer_national_code($payer_national_code)
    {
        $this->payer_national_code = $payer_national_code;
    }

    public function set_client_reference_number($client_reference_number)
    {
        $this->client_reference_number = $client_reference_number;
    }


    public function set_description($description)
    {
        $this->description = $description;
    }

    public function set_additional_data($additional_data)
    {
        $this->additional_data = $additional_data;
    }


    //====================================== main public methods ===============================//

    public function send_pay_request()
    {
        $res_token = $this->generateToken();

        $this->update_pay_request('pay_request_res_token', json_encode($res_token));
        if ($res_token['success'])
        {
            $options = $this->pay_request_options();

            $this->update_pay_request('send_pay_request_send_data', json_encode($options));

            $data = array_diff_key($options, array_flip(['apiKey', 'accessToken']));

            $requestResult = $this->callCurl('/purchases', $data, true);

            $this->update_pay_request('send_pay_request_receive_data', json_encode($requestResult));

            if (!empty($requestResult['pspSwitchingUrl']))
            {
                $this->update_pay_request('pay_request_status', 'success');
                $this->update_pay_request('order_identifier', $requestResult['purchaseId']);
                $this->update_pay_request('redirect_pay_url', $requestResult['pspSwitchingUrl']);
            }
            else
            {
                $this->update_pay_request('pay_request_status', 'error');

                if (!empty($requestResult['errors']))
                {
                    $this->update_pay_request('pay_request_errors', json_encode($requestResult['errors']));
                    $this->update_pay_request('pay_request_error_no', -100);
                    $this->update_pay_request('pay_request_error_msg', 'خطا در ارتباط صحیح با درگاه پرداخت!');
                }
                else
                {
                    $this->update_pay_request('pay_request_error_no', -200);
                    $this->update_pay_request('pay_request_error_msg', 'خطا در پردازش اطلاعات دریاقتی از دروازه پرداخت! ');
                }
            }
        }
        else
        {
            $this->update_pay_request('pay_request_status', 'error');
            $this->update_pay_request('pay_request_error_no', -300);
            $this->update_pay_request('pay_request_error_msg', 'خطا در تولید توکن !');
        }

        return $this->pay_request;
    }

    public function send_pay_verify()
    {
        $order_identifier = $this->pay_request->order_identifier;

        $options =
            [
                'api_key'          => $this->apiKey,
                'order_identifier' => $order_identifier
            ];

        $this->update_pay_request('send_pay_verify_send_data', json_encode($options));

        $res_token = $this->generateToken();

        $this->update_pay_request('pay_verify_res_token', json_encode($res_token));

        if ($res_token['success'])
        {
            $data = [];

            $requestResult = $this->callCurl('/purchases/' . $order_identifier . '/verify', $data, true, 0, 'GET');

            $this->update_pay_request('send_pay_verify_receive_data', json_encode($requestResult));

            if (!empty($requestResult['status']))
            {
                if (strtoupper($requestResult['status']) === 'SUCCESSFUL')
                {
                    $this->update_pay_request('pay_verify_status', 'success');
                }
                else
                {
                    switch ($requestResult['status'])
                    {
                        case 'ALREADY_VERIFIED':
                            $error_no = -100;
                            $error_msg = 'تراکنش قبلا تایید شده است!';
                            break;
                        case 'NOT_VERIFIABLE':
                            $error_no = -101;
                            $error_msg = 'تراکنش قابل بررسی نمی باشد!';
                            break;
                        case 'REVERSED':
                            $error_no = -102;
                            $error_msg = 'تراکنش عودت داده شده است!';
                            break;
                        case 'FAILED':
                            $error_no = -103;
                            $error_msg = 'تراکنش ناموفق بوده است!';
                            break;
                        case 'UNKNOWN':
                            $error_no = -104;
                            $error_msg = 'تراکنش قابل شناسایی نیست!';
                            break;
                        default:
                            $error_no = -105;
                            $error_msg = 'مقدار برگشتی وضعیت تراکنش نامعتبر است!!!';
                    }

                    if (!empty($requestResult['errors']))
                    {
                        $this->update_pay_request('pay_verify_errors', json_encode($requestResult['errors']));
                    }

                    $this->update_pay_request('pay_verify_status', 'error');
                    $this->update_pay_request('pay_verify_error_no', $error_no);
                    $this->update_pay_request('pay_verify_error_msg', $error_msg);
                }
            }
            else
            {
                $this->update_pay_request('pay_verify_status', 'error');
                $this->update_pay_request('pay_verify_error_no', -200);
                $this->update_pay_request('pay_verify_error_msg', 'خطا در پردازش نتیجه استعلام صحت اطلاعات پرداخت!');
            }
        }
        else
        {
            $this->update_pay_request('pay_verify_status', 'error');
            $this->update_pay_request('pay_verify_error_no', -300);
            $this->update_pay_request('pay_verify_error_msg', 'خطا در تولید توکن !');
        }

        return $this->pay_request;
    }


    /**
     * @return mixed
     */
    private function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     */
    private function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param mixed $refreshToken
     */
    private function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }


    /**
     * @param bool $isForce
     * @return array
     * @throws Exception
     */
    public function generateToken($isForce = false)
    {
        $this->cache->eraseExpired();

        $res =
            [
                'success'   => false,
                'error_no'  => -100,
                'error_msg' => 'in_generate_function_has_error'
            ];

        if ($isForce === false && $this->cache->isCached('accessToken'))
        {
            $this->setAccessToken($this->cache->retrieve('accessToken'));

            $res =
                [
                    'success'       => true,
                    'access_token'  => $this->accessToken,
                    'refresh_token' => $this->refreshToken,
                ];
        }
        else
        {
            if ($this->cache->isCached('refreshToken'))
            {
                $res = $refreshToken = $this->refreshTokens();

                if ($refreshToken['success'] === false)
                {
                    $res = $this->generateNewToken();
                }
            }
            else
            {
                $res = $this->generateNewToken();
            }
        }

        return $res;
    }

    private function refreshTokens()
    {
        $data =
            [
                'accessToken'  => str_replace('Bearer ', '', $this->cache->retrieve('accessToken')),
                'refreshToken' => $this->cache->retrieve('refreshToken'),
            ];

        $result = $this->callCurl('/tokens/refresh', $data, false);

        if (empty($result['accessToken']))
        {
            return
                [
                    'success'   => false,
                    'error_no'  => -1,
                    'error_msg' => 'empty_access_token_filed'
                ];
        }

        if (!empty($result['accessToken']))
        {
            $this->cache->store('accessToken', 'Bearer ' . $result['accessToken'], 24 * 60 * 60 - 60);
            $this->cache->store('refreshToken', $result['refreshToken'], 48 * 60 * 60 - 60);
            $this->setAccessToken('Bearer ' . $result['accessToken']);
            $this->setRefreshToken($result['refreshToken']);

            return
                [
                    'success'       => true,
                    'refresh_token' => $result['refreshToken'],
                    'access_token'  => 'Bearer ' . $result['accessToken'],
                ];
        }

        return
            [
                'success'   => false,
                'error_no'  => -1,
                'error_msg' => 'unexcepted_error_in_generate_new_token'
            ];
    }

    private function generateNewToken()
    {
        $data = [
            'apiKey'    => $this->apiKey,
            'secretKey' => $this->secretKey,
        ];

        $result = $this->callCurl('/tokens', $data);

        if (empty($result['accessToken']))
        {
            return
                [
                    'success'   => false,
                    'error_no'  => -1,
                    'error_msg' => 'empty_access_token_filed'
                ];
        }

        if (!empty($result['accessToken']))
        {
            $this->cache->store('accessToken', 'Bearer ' . $result['accessToken'], 24 * 60 * 60 - 60);
            $this->cache->store('refreshToken', $result['refreshToken'], 48 * 60 * 60 - 60);
            $this->setAccessToken('Bearer ' . $result['accessToken']);
            $this->setRefreshToken($result['refreshToken']);

            return
                [
                    'success'       => true,
                    'refresh_token' => $result['refreshToken'],
                    'access_token'  => 'Bearer ' . $result['accessToken'],
                ];
        }

        return
            [
                'success'   => false,
                'error_no'  => -1,
                'error_msg' => 'unexcepted_error_in_generate_new_token'
            ];
    }


    /**
     * @param $id
     * @return bool|mixed|string
     * @throws Exception
     */
    public function getOrderById($id, $timeout)
    {
        return $this->callCurl('/purchases?purchaseId=' . $id, [], true, 0, 'GET', $timeout);
    }

    /**
     * @param $url
     * @param $arrayData
     * @param bool $haveAuth
     * @param int $try
     * @param string $method
     * @return bool|mixed|string
     * @throws Exception
     */
    private function callCurl($url, $arrayData, $haveAuth = false, $try = 0, $method = 'POST',$timeout = null)
    {
        $jsonData = json_encode($arrayData);

        $accessToken = '';
        if ($haveAuth)
        {
            $accessToken = $this->getAccessToken();
        }
        $fullData = $this->base_url . $url;
        $ch = curl_init($fullData);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Jibit.class Rest Api');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($timeout)
        {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $accessToken,
            'Content-Length: ' . strlen($jsonData)
        ]);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true);
        curl_close($ch);

        if ($err)
        {
            return $err;
        }
        else
        {
            if ($haveAuth === true && isset($result['errors']) && $result['errors'][0]['code'] === 'security.auth_required')
            {
                $this->generateToken(true);
                if ($try === 0)
                {
                    return $this->callCurl($url, $arrayData, $haveAuth, 1, $method);
                }
            }

            return $result;
        }
    }
}
