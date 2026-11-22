<?php


namespace App\Http\Helpers\Classes\SmsAPI\SMSIR;


class SMSIR_GetCredit extends SMSIR
{
    /**
     * Gets API Ultra Fast Send Url.
     *
     * @return string Indicates the Url
     */
    protected function getAPIGetCreditUrl()
    {
        return "v1/credit";
    }

    /**
     * Ultra Fast Send Message.
     *
     * @param data[] $data array structure of message data
     *
     * @return string Indicates the sent sms result
     */
    public function getCredit()
    {
        $url = $this->API_URL . $this->getAPIGetCreditUrl();
        $UltraFastSend = $this->_execute($url, false, 'GET');
        $object = json_decode($UltraFastSend);
        if (is_object($object))
        {
            $result = $object->data ?? false;
        }
        else
        {
            $result = false;
        }

        return $result;
    }
}