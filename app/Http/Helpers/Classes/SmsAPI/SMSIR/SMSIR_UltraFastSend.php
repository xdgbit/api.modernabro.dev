<?php


namespace App\Http\Helpers\Classes\SmsAPI\SMSIR;


class SMSIR_UltraFastSend extends SMSIR
{
    /**
     * Gets API Ultra Fast Send Url.
     *
     * @return string Indicates the Url
     */
    protected function getAPIUltraFastSendUrl()
    {
        return "v1/send/verify";
    }

    /**
     * Ultra Fast Send Message.
     *
     * @param data[] $data array structure of message data
     *
     * @return string Indicates the sent sms result
     */
    public function ultraFastSend($data)
    {
        $url = $this->API_URL . $this->getAPIUltraFastSendUrl();

        $UltraFastSend = $this->_execute($url, $data);
        return json_decode($UltraFastSend);
    }
}