<?php

namespace App\Jobs;

use App\Models\Sys\SysData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\SysLog\SysLogSentSMS;
use App\Http\Helpers\Classes\SmsAPI\SMSIR\SMSIR_UltraFastSend;

/**
 * @property  users
 */
class SendUltraFastSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param int $verify_code
     * @param string $mobile_number
     */
    private $parameters;
    private $template_id;
    private $mobile_number;
    private $for_reason;

    private $service_name = 'smsir_ultra_fast';
    public $sys_log_sent_sms;

    /**
     * SendUltraFastSMS constructor.
     * @param array $parameters
     * @param $template_id
     * @param $mobile_number
     * @param $for_reason
     */
    public function __construct(array $parameters, $template_id, $mobile_number, $for_reason)
    {
        $this->parameters = $parameters;
        $this->template_id = $template_id;
        $this->mobile_number = $mobile_number;
        $this->for_reason = $for_reason;

//        $this->sys_log_sent_sms = $this->create_sent_log();
//        $this->plus_sys_data_sms_count();
    }

    private function plus_sys_data_sms_count()
    {
        $sys_data_sms = SysData::where('dev_title', 'sent_sms_irr_total_count')->first();
        if (!$sys_data_sms)
        {
            $sys_data_sms = new SysData();
            $sys_data_sms->title = 'تعداد پیامک ارسالی';
            $sys_data_sms->dev_title = 'sent_sms_irr_total_count';
            $sys_data_sms->value = 1;
        }
        else
        {
            $count = $sys_data_sms->value;
            $sys_data_sms->value = (double)$count + 1;
        }
        $sys_data_sms->save();

    }

    private function create_sent_log(): SysLogSentSMS
    {
        $sys_log_sent_email = new  SysLogSentSMS();
        $sys_log_sent_email->set_default_data();
        $sys_log_sent_email->to = $this->mobile_number;
        $sys_log_sent_email->for_reason = $this->for_reason;
        $sys_log_sent_email->msg_content = $this->get_content();
        $sys_log_sent_email->from_service_name = $this->service_name;
        $sys_log_sent_email->send_params = $this->get_send_params();
        $sys_log_sent_email->save();

        return $sys_log_sent_email;
    }

    private function get_send_params()
    {
        $params = $this->get_data();

        return json_encode($params);
    }

    private function get_template($template_id)
    {
        switch ((string)$template_id)
        {
            //---------------------------------------------------Mobile Verify--------------------------------------------------------
            case '42296':
            {
                $template = 'کد تایید: [VerifyCode] جهت احراز مالکیت شماره همراه در سامانه dgbit.com';
                break;
            }
            //------------------------------------------ Authentication User Customers -----------------------------------------------
            case '49126':
            {
                $template = 'احراز هویت کامل شما با شناسه کاربری [XdgbitCustomerID] در سامانه xdgbit انجام گردید.';
                break;
            }
            case '49125':
            {
                $template = 'مشخصات فردی شما به شناسه کاربری [XdgbitCustomerID]در سامانه xdgbit مورد تایید کارشناس قرار نگرفت لطفا پس از بررسی صحت اطلاعات خود مجدد اقدام نمایید.';
                break;
            }
            case '49127':
            {
                $template = 'فیلم سلفی شما به شناسه کاربری [XdgbitCustomerID] در سامانه xdgbit مورد تایید کارشناس قرار نگرفت، لطفا مطابق شرایط خواسته شده، مجدد اقدام نمایید.';
                break;
            }
            case '49128':
            {
                $template = 'کارت بانکی شما به شناسه [BankAcountID]در سامانه xdgbit مورد تایید قرار نگرفت، دقت نمایید کارت ثبت شده باید معتبر و متعلق به خودتان باشد.';
                break;
            }

            //--------------------------------------------------- BFU --------------------------------------------------------
            case '49123':
            {
                $template = 'سفارش خرید به شماره [InvoiceNumber] در سامانه xdgbit، مورد تایید قرار نگرفت.';
                break;
            }
            case '49152':
            {
                $template = 'سفارش خرید از ما به شماره [InvoiceNumber] در سامانه xdgbit تایید و انجام گردید.';
                break;
            }

            //--------------------------------------------------- STU --------------------------------------------------------
            case '49124':
            {
                $template = 'سفارش فروش به شماره [InvoiceNumber] در سامانه xdgbit، مورد تایید قرار نگرفت.';
                break;
            }
            case '49151':
            {
                $template = 'سفارش فروش به ما به شماره [InvoiceNumber] در سامانه xdgbit تایید و انجام گردید.';
                break;
            }


            //--------------------------------------------------- Withdrawal --------------------------------------------------------
            case '49130':
            {
                $template = 'تقاضای برداشت شما به شناسه [InvoiceNumber] در سامانه xdgbit مورد تایید قرار نگرفت.';
                break;
            }
            case '49131':
            {
                $template = 'تقاضای برداشت شما به شناسه [InvoiceNumber] در سامانه xdgbit تایید و اعمال گردید.';
                break;
            }


            //--------------------------------------------------- Deposit Direct --------------------------------------------------------
            case '49132':
            {
                $template = 'شارژ مستقیم به شناسه [InvoiceNumber] در سامانه xdgbit مورد تایید قرار نگرفت.';
                break;
            }
            case '49133':
            {
                $template = 'شارژ مستقیم به شناسه [InvoiceNumber] در سامانه xdgbit تایید و اعمال گردید.';
                break;
            }


            //--------------------------------------------------- Tickets --------------------------------------------------------
            case '49134':
            {
                $template = 'تیکت شما به شناسه [TicketNumber] در سامانه xdgbit ثبت گردید.';
                break;
            }
            case '49135':
            {
                $template = 'تیکت شما به شناسه [TicketNumber] در سامانه xdgbit پاسخ داده شد.';
                break;
            }
            case '49136':
            {
                $template = 'تیکت شما به شناسه [TicketNumber] در سامانه xdgbit بسته شد.';
                break;
            }


            //--------------------------------------------------- Notification Direct By Expert To Specific Or Selection --------------------------------------------------------
            case '49137':
            {
                $template = 'اطلاعیه به شماره [NotificationNumber] در سامانه xdgbit ویژه شما ایجاد گردیده است لطفا بررسی بفرمایید.';
                break;
            }


            //--------------------------------------------------- System Alert To Admin --------------------------------------------------------
            case '20205':
            {
                $template = 'تلاش های کاربر با شناسه [UserID] و به شماره همراه [UserMobile] جهت تایید موبایل به حد روزانه رسید.';
                break;
            }
            case '20206':
            {
                $template = 'تلاش های کاربر با شناسه [UserID] و به شماره همراه [UserMobile] جهت تایید موبایل به حد کل رسید.';
                break;
            }
            case '49145':
            {
                $template = 'جهت خرید محصول به شناسه [ProductID] معادل [TotalPrice] توسط کاربر به شناسه [XdgbitCustomerID] شارژ حساب کافی نمی باشد.';
                break;
            }
            //-----------------------------------------------------login expert------------------------------------------------------------------
            case '53883':
            {
                $template = 'کارشناس [ExpertFullName] با آی پی [ExpertIP] وارد پنل کارشناسی شد.';
                break;
            }
            case '53884':
            {
                $template = 'کد تایید: [VerifyCode] جهت ورود به سامانه کارشناسان xdgbit';
                break;
            }
            case '60837':
            {
                $template = 'تلاش ناموفق برای ورود [UserType] از طریق مسیر ویژه توسط IP: [ClientIP] ثبت گردید.';
                break;
            }
            case '60838':
            {
                $template = 'ورود به بخش [UserType] از طریق مسیر ویژه با [UserID]:UID از IP:[ClientIP] انجام شد.';
                break;
            }


            case '61602':
            {
                $template = 'بروزرسانی داده های محاسباتی برای [DCSymbol] باخطا از نوع [Type] مواجه شد.';
                break;
            }

//            case '82349':
//            {
//                $template = 'عکس سلفی با ولت شما به شناسه کاربری [XdgbitCustomerID]در سامانه xdgbit مورد تایید کارشناس قرار نگرفت لطفا پس از بررسی صحت اطلاعات خود مجدد اقدام نمایید.';
//                break;
//            }

            case '82349':
            {
                $template = 'کد تایید : [VerifyCode] جهت تایید برداشت ارزی xdgbit.com';
                break;
            }

            case '83192':
            {
                $template = 'کد تایید : [VerifyCode] جهت تایید برداشت ریالی xdgbit.com';
                break;
            }

            default:
            {
                $template = '';
            }
        }

        return $template;
    }

    private function get_content($template_id = false, $parameters = false)
    {
        if ($template_id === false)
        {
            $template_id = $this->template_id;
        }

        if ($parameters === false)
        {
            $parameters = $this->parameters;
        }

        $template = $this->get_template($template_id);

        $finds = [];
        $keys = array_keys($parameters);
        foreach ($keys as $key)
        {
            $finds[] = '[' . $key . ']';
        }

        $replace = array_values($parameters);

        return str_ireplace($finds, $replace, $template);
    }

    private function generate_parameter_array($parameters = false)
    {
        $prepared_array = [];
        $parameters = $parameters ? $parameters : $this->parameters;
        foreach ($parameters as $key => $val)
        {
            $prepared_array[] =
                [
                    'name'  => $key,
                    'value' => $val
                ];
        }

        return $prepared_array;
    }

    private function get_data()
    {
        return
            [
                "mobile"     => $this->mobile_number,
                "templateId" => $this->template_id,
                'parameters' => $this->generate_parameter_array(),
            ];
    }

    private function save_receive_response_log($response)
    {
        $log = $this->sys_log_sent_sms;
        $log->receive_response = json_encode($response);
        $log->save();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            $SMSIR_UltraFastSend = new SMSIR_UltraFastSend();
            $response = $SMSIR_UltraFastSend->ultraFastSend($this->get_data());
//            $this->save_receive_response_log($response);
        } catch (\Exception $exception)
        {
//            $this->save_receive_response_log($exception);
        }
    }
}
