<?php


namespace App\Http\Helpers\Classes\SmsAPI\SMSIR;


class SMSIR
{
    public $API_URL;
    public $API_Key;
    /**
     * Gets config parameters for sending request.
     *
     * @param bool $API_URL
     * @param bool $API_Key
     * @param bool $Secret_Key
     */
    public function __construct($API_URL = false, $API_Key = false, $Secret_Key = false)
    {
        $this->API_URL = $API_URL ? $API_URL : config('sms_panels.sms_ir.ultra_fast_method.api_url');
        $this->API_Key = $API_Key ? $API_Key : config('sms_panels.sms_ir.ultra_fast_method.api_key');
    }

    /**
     * Executes the main method.
     *
     * @param string $url url
     * @param bool $postData array of json data
     * @return string Indicates the curl execute result
     */
    public function _execute(string $url, $postData = false, $method = 'POST')
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: text/plain',
                "x-api-key: $this->API_Key"
            ],
        ]);
        if ($postData)
        {
            $postString = json_encode($postData);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postString);
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }


}