<?php


if (!function_exists('d'))
{
    function d(...$args)
    {
        http_response_code(500);
        dd($args);
    }
}

if (!function_exists('ConvertNumbersEntoFa'))
{
    function ConvertNumbersEntoFa($matches)
    {
        $farsi_array = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english_array = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($english_array, $farsi_array, $matches);
    }
}

if (!function_exists('ConvertNumbersFatoEn'))
{
    function ConvertNumbersFatoEn($matches)
    {
        $farsi_array = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english_array = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($farsi_array, $english_array, $matches);
    }
}

if (!function_exists('calculate_referral_reward'))
{
    function calculate_referral_reward($irr_amount, $referrer_user_customer = null)
    {
        if ($referrer_user_customer)
        {
            if ($referrer_user_customer->referral_ratio)
            {
                return round($irr_amount * $referrer_user_customer->referral_ratio);
            }
            else
            {
                $last_sys_configs = \App\Models\SystemConfigs::notArchive()->last()->first();
                $ratio_referral = $last_sys_configs->ratio_referral;

                return round($irr_amount * $ratio_referral);
            }
        }
        else
        {
            $last_sys_configs = \App\Models\SystemConfigs::notArchive()->last()->first();
            $ratio_referral = $last_sys_configs->ratio_referral;

            return round($irr_amount * $ratio_referral);
        }
    }
}

if (!function_exists('asset_version'))
{
    function asset_version()
    {
        return '?v=2026_10_06_v1';
    }
}

if (!function_exists('floor_decimals'))
{
    function floor_decimals($val, $precision)
    {
        $mult = pow(10, $precision); // Can be cached in lookup table

        return precise_div(floor(precise_mul($val, $mult)), $mult);
    }
}
if (!function_exists('round_step'))
{
    function round_step($amount, $available_decimals)
    {
        $half = 0.5 / pow(10, $available_decimals); // Can be cached in a lookup table

        return round($amount - $half, $available_decimals);
    }
}

if (!function_exists('OpenModalAuthenticationStatus'))
{
    function OpenModalAuthenticationStatus()
    {
        $route_not_open =
            [
                'customer.account.bank_accounts.index',
                'customer.account.profile.index.display_tab',
                'customer.authentication.personal_info.index',
                'customer.authentication.bank_accounts.index',
                'customer.authentication.verify_mobile.index',
                'customer.authentication.verify_phone.index',
                'customer.authentication.documents.index',
                'customer.authentication.pending.full.index',
                'customer.authentication.pending.basic.index',
                'auth.verify.email.callback',
                'auth.verify.email.index',
                'customer.account.notifications.index',
                'customer.account.reset_password.index',
                'customer.baned_page',
            ];
        $just_basic_auth_needed_routs =
            [
                'customer.store.index',
                'customer.store.sell_to_us.index',
            ];
        $current_route_name = request()->route()->getName();
        $user_customer = auth()->guard('user_customer')->user();
        if ($user_customer)
        {
            $full_authentication_status = $user_customer->full_authentication_status;
            $basic_authentication_status = $user_customer->basic_authentication_status;
            switch ($full_authentication_status)
            {
                case 'approved':
                case 'pending':
                    return false;
                    break;
                case 'rejected':
                    if (in_array($current_route_name, $route_not_open))
                    {
                        return false;
                    }
                    else
                    {
                        return true;
                    }
                    break;
                case 'none':
                default:
                    switch ($basic_authentication_status)
                    {
                        case 'approved':
                        case 'pending':
                            if (in_array($current_route_name, $just_basic_auth_needed_routs))
                            {
                                return false;
                            }
                            else
                            {
                                if (in_array($current_route_name, $route_not_open))
                                {
                                    return false;
                                }
                                else
                                {
                                    return true;
                                }
                            }
                            break;
                        case 'rejected':
                        case 'none':
                        default:
                            if (in_array($current_route_name, $route_not_open))
                            {
                                return false;
                            }
                            else
                            {
                                return true;
                            }
                            break;
                    }
            }
        }
        else
        {
            return false;
        }
    }
}

if (!function_exists('array_field_name'))
{
    function array_field_name($key)
    {
        $key_name_parts = explode('.', $key);
        $res = $key_name_parts[0];
        foreach ($key_name_parts as $k => $part)
        {
            if ($k > 0)
            {
                $res .= '[' . $part . ']';
            }
        }

        return $res;
    }
}

if (!function_exists('custom_number_format'))
{
    function custom_number_format($number, $custom_decimal = false)
    {
        if ($number < 1000)
        {
            if ($number < 1)
            {
                $number = sprintf('%.14f', doubleval($number));
            }
            else
            {
                $number = sprintf('%.14G', doubleval($number));
            }
        }
        else
        {
            $number = strval($number);
        }

        if (!strrchr($number, "."))
        {
            return number_format($number);
        }
        else
        {
            $x = explode('.', $number);

            if ($custom_decimal === 0)
            {
                return number_format($x[0]);
            }

            if ($custom_decimal && $custom_decimal < strlen($x[1]))
            {
                $x[1] = substr($x[1], 0, $custom_decimal);
            }

            $exists = false;
            $arr_digits = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];


            if (isset($x[1]) && (int)$x[1] != 0)
            {
                foreach ($arr_digits as $digit)
                {
                    if (strrchr($x[1], $digit))
                    {
                        $exists = true;
                    }
                }

                if ($exists)
                {

                    $start_len = strlen($x[1]);
                    for ($i = 1; $i < $start_len; $i++)
                    {
                        $last_char = substr($x[1], -1);
                        if ($last_char == 0)
                        {
                            $x[1] = substr($x[1], 0, -1);
                        }
                        elseif ($last_char == '.' || $last_char !== 0)
                        {
                            break;
                        }
                    }

                    $sign = $x[0] === '-0' ? '-' : '';

                    $a = number_format($x[0]) . '.' . $x[1];

                    return $sign . $a;
                }
                else
                {
                    return number_format($x[0]);
                }
            }
            else
            {
                return number_format($x[0]);
            }
        }
        //}
    }
}

if (!function_exists('remove_script_tags_for_eval'))
{
    function remove_script_tags_for_eval($scripts_str = '<script>console.log("hello! eval(script) ")</script>')
    {
        //$scripts_str = str_replace("<script>", '', $scripts_str);
        //$scripts_str = str_replace("</script>", '', $scripts_str);
        $scripts_str = str_replace(['<script>', '<script type="text/javascript">', '</script>',], null, $scripts_str);

        return $scripts_str;
        /*$matches = preg_replace('#<script>]*>([^<]+)</script>#', '$1', $scripts_str);

        return $matches;*/
    }
}

if (!function_exists('validation_error_to_api_json'))
{
    function validation_error_to_api_json($errors)
    {
        $api_errors = [];
        foreach ($errors->getMessages() as $key => $value)
        {
            $key = array_field_name($key);
            $api_errors[ $key ] = array_values($value);
        }

        return $api_errors;
    }
}

if (!function_exists('sys_encode_id'))
{
    function sys_encode_id($id, $length = 10)
    {
        $hash_ids = new \Hashids\Hashids(config('constants.hash_ids.salt'), $length);

        return $hash_ids->encode($id);
    }
}

if (!function_exists('sys_decode_id'))
{
    function sys_decode_id($encoded_id, $length = 10)
    {
        $hash_ids = new \Hashids\Hashids(config('constants.hash_ids.salt'), $length);
        $decodes = $hash_ids->decode($encoded_id);

        if (isset($decodes[0]))
        {
            return $decodes[0];
        }
        else
        {
            return null;
        }
    }
}

if (!function_exists('is_set_developer_token'))
{
    function is_set_developer_token()
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            session_start();
        }

        if (isset($_SESSION['developer_token']))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

if (!function_exists('api_buy_order_binance'))
{
    function api_buy_order_binance($symbol, $quantity, $invoice)
    {
        $active_api = \App\Models\Sys\ExchangeAPI\SysExchangeAPI::notArchive()->last()->first();
        switch ($active_api->api_name)
        {
            case 'binance':
            {
                $binance = new \App\Http\Helpers\Classes\ExchangeAPI\Binance();
                $binance->buy_order($symbol, $quantity, $invoice);

                return true;
                break;
            }
            case 'binance2':
            {
                $binance2 = new \App\Http\Helpers\Classes\ExchangeAPI\Binance2();
                $binance2->buy_order($symbol, $quantity, $invoice);

                return true;
                break;
            }
            case 'poloniex':
            {
                $poloniex = new \App\Http\Helpers\Classes\ExchangeAPI\Poloniex();
                $poloniex->buy_order($symbol, $quantity, $invoice);

                return true;
                break;
            }
            default:
                return false;
        }
    }
}

if (!function_exists('api_sell_order_binance'))
{
    function api_sell_order_binance($symbol, $quantity, $invoice)
    {
        $active_api = \App\Models\Sys\ExchangeAPI\SysExchangeAPI::notArchive()->last()->first();
        switch ($active_api->api_name)
        {
            case 'binance':
            {
                $binance = new \App\Http\Helpers\Classes\ExchangeAPI\Binance();
                $binance->sell_order($symbol, $quantity, $invoice);

                return true;
                break;
            }
            case 'binance2':
            {
                $binance2 = new \App\Http\Helpers\Classes\ExchangeAPI\Binance2();
                $binance2->sell_order($symbol, $quantity, $invoice);

                return true;
                break;
            }
            case 'poloniex':
            {
                $poloniex = new \App\Http\Helpers\Classes\ExchangeAPI\Poloniex();
                $poloniex->sell_order($symbol, $quantity, $invoice);

                return true;
                break;
            }
            default:
                return false;
        }
    }
}

if (!function_exists('update_deposit_binance_histories'))
{
    function update_deposit_binance_histories()
    {
        $active_api = \App\Models\Sys\ExchangeAPI\SysExchangeAPI::notArchive()->last()->first();
        switch ($active_api->api_name)
        {
            case 'binance':
            {
                $binance = new \App\Http\Helpers\Classes\ExchangeAPI\Binance();
                $binance->update_deposit_histories();

                return true;
                break;
            }
            case 'poloniex':
            {
                $poloniex = new \App\Http\Helpers\Classes\ExchangeAPI\Poloniex();
                $poloniex->update_deposit_histories();

                return true;
                break;
            }
            default:
                return false;
        }

    }
}

if (!function_exists('update_all_digital_currencies_price'))
{
    function update_all_digital_currencies_price()
    {
        $active_api = \App\Models\Sys\ExchangeAPI\SysExchangeAPI::notArchive()->last()->first();
        switch ($active_api->api_name)
        {
            case 'binance':
            {
                $binance = new \App\Http\Helpers\Classes\ExchangeAPI\Binance();
                $binance->update_all_digital_currencies_prices();

                return true;
                break;
            }
            case 'poloniex':
            {
                $poloniex = new \App\Http\Helpers\Classes\ExchangeAPI\Poloniex();
                $poloniex->update_all_digital_currencies_prices();

                return true;
                break;
            }
            default:
                return false;
        }
    }
}





if (!function_exists('join_wav_files'))
{
    function join_wav_files($file_names, $out_put_name = 'out_put_merged.wav', $out_put_path = '_generated_verify_phone_files/')
    {
        $fields = join('/', ['H8ChunkID', 'VChunkSize', 'H8Format',
            'H8Subchunk1ID', 'VSubchunk1Size',
            'vAudioFormat', 'vNumChannels', 'VSampleRate',
            'VByteRate', 'vBlockAlign', 'vBitsPerSample']);
        $data = '';
        foreach ($file_names as $file_name)
        {
            $fp = fopen(Storage::disk('local')->getAdapter()->getPathPrefix() . 'verify_phone_waves/' . $file_name, 'rb');//binary read
            $header = fread($fp, 36); //get the header of the file
            $info = unpack($fields, $header); //unpacks the parts of a binary string

            //into an array as follows:
            /*

            goes like so: dataTypeHOWMANYKey_To_Use_When_Storing_it
            So the below says "Read 8 (whatever H is) and store in ChunkID1, ChunkID2, etc... ChunkID8
            inside of the associative array.

            'H8ChunkID',            => H Hex string high nibble first
            'VChunkSize',           => V unsigned long (always 32 bit, little endian byte order)
            'H8Format',             => H Hex string high nibble first
            'H8Subchunk1ID',        => H Hex string high nibble first
            'VSubchunk1Size',       => V unsigned long (always 32 bit, little endian byte order)
            'vAudioFormat',         => v unsigned short (always 16 bit, little endian byte order)
            'vNumChannels',         => v unsigned short (always 16 bit, little endian byte order)
            'VSampleRate',          => V unsigned long (always 32 bit, little endian byte order)
            'VByteRate',            => V unsigned long (always 32 bit, little endian byte order)
            'vBlockAlign',          => v unsigned short (always 16 bit, little endian byte order)
            'vBitsPerSample'        => v unsigned short (always 16 bit, little endian byte order)

            */

            // read optional extra stuff
            //is this really all that $info is used for...??
            if ($info['Subchunk1Size'] > 16)
            {
                //only one because just V not V2 or V8 etc
                $header .= fread($fp, ($info['Subchunk1Size'] - 16)); //idk read/add the extra stuff into header I guess
                //would probably help to read the file format spec
            }

            // read SubChunk2ID
            $header .= fread($fp, 4);
            // read Subchunk2Size
            $size = unpack('Vsize', fread($fp, 4));
            $size = $size['size'];
            // read data
            while (!feof($fp))
            {
                $data .= fread($fp, 1024 * 8); //fixes the below part
            }
            //$data .= fread($fp,$size);
            fclose($fp);
        }
        $filecontent = $header . pack('V', strlen($data)) . $data;
        $fp1 = fopen($out_put_path . $out_put_name, "wb");
        fwrite($fp1, $filecontent);
        fclose($fp1);

        return url($out_put_path . $out_put_name);
    }

}

if (!function_exists('generate_verify_voice_text'))
{
    function generate_verify_voice_text($code)
    {
        $text = ',با سلام, کد احراز مالکیت تلفنِ ثابتِ شما در سامانه, ایکس دی جی بْیت,';

        $split_code_digits = str_split($code);
        $code_str = '';
        foreach ($split_code_digits as $key => $digit)
        {

            if ((int)fmod($key - 1, 2) === 0)
            {
                $code_str .= $digit . ',';
            }
            else
            {
                $code_str .= $digit;
            }
        }
        $text .= $code_str;
        $text .= ',می باشد. ,تکرار کدِ احرازْ, ';
        $text .= $code_str;
        $text .= ',خدانگهدار.';

        return $text;
    }

}

if (!function_exists('response_exception_error'))
{
    function response_exception_error($error_no = '-500')
    {

        return
            [
                'success'   => false,
                'error_no'  => $error_no,
                'msg_type'  => 'error',
                'msg_title' => 'در ثبت اطلاعات خطا روی داده است.',
                'msg_text'  => 'لطفا دوباره سعی کنید و در صورت تکرار این خطا لطفا با مدیریت تماس حاصل فرمایید.',
                //'msg_title' => 'خطا',
                //'msg_text'  => 'خطایی در پردازش اطلاعات روی داده است.',
            ];
    }
}

if (!function_exists('get_expert_common_event_channel'))
{
    function get_expert_common_event_channel()
    {
        return 'expert_common_event_channel_md5_' . md5(\Carbon\Carbon::now()->format('Y-m-d') . 'Expert');
    }
}

if (!function_exists('get_super_admin_common_event_channel'))
{
    function get_super_admin_common_event_channel($check_guard = false)
    {
        if ($check_guard)
        {
            if (!JustSuperAdmin())
            {
                return 'super_admin_common_event_channel_md5_not_super_admin';
            }
        }

        return 'super_admin_common_event_channel_md5_' . md5(\Carbon\Carbon::now()->format('Y-m-d') . '%^RFVYGUIksfgjkwgurisabdfjuilbv3');
    }
}

if (!function_exists('badge_data'))
{
    function badge_data()
    {
        $bfu_visit = \App\Models\BuyFromUs\BFU_Invoice::where('invoice_status', 'pending_for_trade')
            ->orWhere('invoice_status', 'pending_for_transfer')->count();
        $stu_visit = \App\Models\SellToUs\STU_Invoice::where('invoice_status', 'pending')->count();
        $withdrawal_visit = \App\Models\Withdrawal\WithdrawalIrr::where('status', 'pending')->count();
        $user_customer_visit = \App\Models\User\Customer\UserCustomer::needApprove()->notBaned()->count();
        $deposit_direct_visit = \App\Models\Deposit\DepositIrrDirectPaymentInvoices::where('status', 'pending')->count();
        $ticket_waiting_for_answer = \App\Models\Ticket\Ticket::where('status', 'pending')->count();

        return
            [
                'stu_badge'                    => $stu_visit,
                'fa_stu_badge'                 => ConvertNumbersEntoFa($stu_visit),
                'bfu_badge'                    => $bfu_visit,
                'fa_bfu_badge'                 => ConvertNumbersEntoFa($bfu_visit),
                'withdrawal_badge'             => $withdrawal_visit,
                'fa_withdrawal_badge'          => ConvertNumbersEntoFa($withdrawal_visit),
                'user_customer_badge'          => $user_customer_visit,
                'fa_user_customer_badge'       => ConvertNumbersEntoFa($user_customer_visit),
                'deposit_direct_badge'         => $deposit_direct_visit,
                'fa_deposit_direct_badge'      => ConvertNumbersEntoFa($deposit_direct_visit),
                'ticket_waiting_for_answer'    => $ticket_waiting_for_answer,
                'fa_ticket_waiting_for_answer' => ConvertNumbersEntoFa($ticket_waiting_for_answer),
            ];
    }
}

if (!function_exists('client_ip'))
{
    function client_ip()
    {
        $ip = request()->ip();

        if (request()->hasHeader('cf-connecting-ip'))
        {
            $ip = request()->header('cf-connecting-ip');
        }
        else
        {
            $ar_real_ip = request()->hasHeader('ar-real-ip');
            if ($ar_real_ip && isset($ar_real_ip[0]))
            {
                $ip = $ar_real_ip[0];
            }
        }

        return $ip;
    }
}

if (!function_exists('restricts_country'))
{
    function restricts_country(...$country_symbols)
    {
        $ip = client_ip();

        if (config('app.debug') || !config('constants.is_check_restricts_country'))
        {
            return true;
        }

        //$method = request()->method();
        //$abort_type = strtolower($method) === 'get' ? 403 : 401;

        if (request()->hasHeader('cf-ipcountry'))
        {
            $country_code = request()->header('cf-ipcountry');
            if (in_array(strtoupper($country_code), $country_symbols))
            {
                return true;
            }
        }
        else
        {
            $country_code = request()->header('ar-real-country');
            if ($country_code && isset($country_code[0]))
            {
                if (in_array(strtoupper($country_code[0]), $country_symbols))
                {
                    return true;
                }
            }
        }


        try
        {
            $s = file_get_contents('http://ip2c.org/' . $ip);

            switch ($s[0])
            {
                case '1':
                    $reply = explode(';', $s);
                    $founded = array_intersect($country_symbols, $reply);
                    if (is_array($founded) && count($founded) > 0)
                    {
                        return true;
                    }
                    break;
                case '0': //'Something wrong';
                case '2':// Not Found In Database
                default:
                    break;
            }

            $s2 = json_decode(file_get_contents('http://ip-api.com/json/' . $ip));
            $country_code = isset($s2->countryCode) ? $s2->countryCode : null;

            if ($country_code && in_array($country_code, $country_symbols))
            {
                return true;
            }
            else
            {
                $s3 = json_decode(file_get_contents('http://ipwhois.app/json/' . $ip));
                $country_code = isset($s3->country_code) ? $s3->country_code : null;

                if ($country_code && in_array($country_code, $country_symbols))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        } catch (\Exception $e)
        {
            return false;
        }
    }
}

if (!function_exists('plus_sys_data_email_count'))
{
    function plus_sys_data_email_count()
    {
        $sys_data_email = \App\Models\Sys\SysData::where('dev_title', 'sent_email_total_count')->first();
        if (!$sys_data_email)
        {
            $sys_data_email = new \App\Models\Sys\SysData();
            $sys_data_email->title = 'تعداد ایمیل ارسالی';
            $sys_data_email->dev_title = 'sent_email_total_count';
            $sys_data_email->value = 1;
        }
        else
        {
            $count = $sys_data_email->value;
            $sys_data_email->value = $count + 1;
        }
        $sys_data_email->save();
    }
}

if (!function_exists('create_notification'))
{
    function create_notification($receiver_user_customer_id, $title, $description, $reason_type, $receiver_type, $prev_code)
    {
        $user_notification = new \App\Models\User\Customer\UserCustomerNotification();
        $user_notification->receiver_type = $receiver_type;
        $user_notification->reason_type = $reason_type;
        $user_notification->receiver_user_customer_id = $receiver_user_customer_id;
        $user_notification->title = $title;
        $user_notification->description = $description;
        $user_notification->published_at = new \Verta();
        $user_notification->expired_at = null;
        $user_notification->submitted_by = -1;
        $user_notification->submitted_at = \Carbon\Carbon::now();
        $user_notification->setComputedFields($prev_code);
        $user_notification->save();

        return $user_notification;
    }
}

if (!function_exists('page_description_generator'))
{
    function page_description_generator($data, $length = 150)
    {
        if ($data)
        {
            $str_len = mb_strlen($data, 'UTF-8');
            if ($str_len <= $length)
            {

                return $data;
            }
            else
            {
                return mb_substr($data, 0, $length, 'utf-8');
            }
        }
        else
        {
            return '';
        }
    }
}

if (!function_exists('page_twitter_description_generator'))
{
    function page_twitter_description_generator($data, $length = 280)
    {
        if ($data)
        {
            $str_len = mb_strlen($data, 'UTF-8');
            if ($str_len <= $length)
            {

                return $data;
            }
            else
            {
                return mb_substr($data, 0, $length, 'utf-8');
            }
        }
        else
        {
            return '';
        }
    }
}

if (!function_exists('str_truncate_middle'))
{
    function str_truncate_middle($text, $maxChars = 20, $filler = ' *** ')
    {
        $length = strlen($text);
        $fillerLength = strlen($filler);

        return ($length > $maxChars)
            ? substr_replace($text, $filler, ($maxChars - $fillerLength) / 2, $length - $maxChars + $fillerLength)
            : $text;
    }
}

if (!function_exists('page_title_generator'))
{
    function page_title_generator($data, $prefix = false)
    {
        if ($data)
        {
            if (!$prefix)
            {
                $prefix = __('frontend/theme.default_page_prefix_title');
            }

            return $prefix . ' ' . $data;
        }
        else
        {
            return '';
        }
    }
}

if (!function_exists('auto_open_user_menu'))
{
    function auto_open_user_menu()
    {
        $current_route_name = request()->route()->getName();
        $user_customer = auth()->guard('user_customer')->user();
        $list_route =
            [
                'customer.dashboard.index',
                'customer.store.index',
            ];
        if ($user_customer->MyUnreadNotificationCount > 0 && in_array($current_route_name, $list_route) && Browser::isMobile())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

if (!function_exists('check_api_token'))
{
    function check_api_token($token)
    {

    }
}

if (!function_exists('dc_supported'))
{
    function dc_supported()
    {
        return ['bnb', 'btc', 'eth', 'shib', 'doge', 'ltc', 'xrp', 'trx', 'ada', 'theta', 'neo', 'chz', 'eos', 'dot', 'link', 'ethbull', 'ethbear', 'metaverse'];
    }
}

if (!function_exists('ConvertStringArabicToFa'))
{
    function ConvertStringArabicToFa($str)
    {
        $str = str_replace('ك', 'ک', $str);
        $str = str_replace('ي', 'ی', $str);

        return $str;
    }
}

if (!function_exists('JustSuperAdmin'))
{
    function JustSuperAdmin($abort = 401)
    {
        $expert = auth()->guard('user_expert')->user();

        if (!$expert || $expert->id != '62')
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}

if (!function_exists('public_pusher_configs'))
{
    function public_pusher_configs()
    {
        $options = config('broadcasting.connections.pusher.options', []);

        return [
            'key'     => config('broadcasting.connections.pusher.key'),
            'host'    => $options['host'] ?? null,
            'port'    => $options['port'] ?? 6001,
            'scheme'  => $options['scheme'] ?? 'http',
            'use_tls' => (bool)($options['useTLS'] ?? false),
            'cluster' => $options['cluster'] ?? null,
        ];
    }
}




if (!function_exists('generate_available_decimals'))
{
    function generate_available_decimals($price)
    {
        if ($price > 0)
        {
            $one_cent_cost = 0.0001 / $price;
            //farsi deghat andazegiti
            if ($one_cent_cost < 1)
            {
                $count = 1;
                while ($one_cent_cost < 1)
                {
                    $count++;
                    $one_cent_cost = $one_cent_cost * 10;
                }

                return $count - 1;
            }
            else
            {
                return 0;
            }
        }
        else
        {
            return 0;
        }
    }
}

if (!function_exists('email_check_disposable'))
{
    function email_check_disposable($value)
    {
        $email = explode('@', $value);
        if (isset($email[1]))
        {
            try
            {
                $request_url = 'https://www.disify.com/api/email/x@' . $email[1];
                $guzzle_client = new \GuzzleHttp\Client();
                $request = $guzzle_client->request('GET', $request_url);
                $response_pay_request = json_decode($request->getBody()->getContents());
                if ($response_pay_request)
                {
                    if ($response_pay_request->disposable === true)
                    {
                        $res =
                            [
                                'success'      => true,
                                'check_result' => false
                            ];
                    }
                    else
                    {
                        $res =
                            [
                                'success'      => true,
                                'check_result' => true
                            ];
                    }
                }
                else
                {
                    $res =
                        [
                            'success'      => false,
                            'check_result' => null
                        ];
                }
            } catch (Exception $exception)
            {
                $res =
                    [
                        'success'      => false,
                        'check_result' => null
                    ];
            }
        }
        else
        {
            $res =
                [
                    'success'      => false,
                    'check_result' => false
                ];
        }

        return $res;
    }
}

if (!function_exists('expert_single_session_login'))
{
    function expert_single_session_login($ip)
    {
        $user_customer = auth()->guard('user_expert')->user();
        $last_session_id = $user_customer->last_session_id;
        if ($last_session_id)
        {
            //session()->getHandler()->destroy($last_session_id);
        }
        $user_customer->last_ip = $ip;
        $user_customer->last_session_id = session()->getId();
        $user_customer->last_user_agent_md5 = md5(request()->userAgent());
        $user_customer->save();
    }

}
if (!function_exists('check_production_channel'))
{
    function check_production_channel($data): string
    {
        $pre = config('app.env') === 'production' ? '' : config('app.env');

        return $pre . $data;
    }
}
if (!function_exists('this_year'))
{
    function this_year(): int
    {
        return \Carbon\Carbon::now()->year;
    }
}
if (!function_exists('is_tax_user'))
{
    function is_tax_user(): string
    {
        if (auth()->guard('user_expert')->check())
        {
            return in_array(auth()->guard('user_expert')->id(), [68]) ? '1' : '0';
        }

        return '0';
    }
}
if (!function_exists('is_limited_expert'))
{
    function is_limited_expert(): string
    {
        $auth_guard_expert = auth()->guard('user_expert');
        if ($auth_guard_expert->check())
        {
            if ($auth_guard_expert->user()->is_limited_expert === '1')
            {
                return '1';
            }
            else
            {
                return '0';
            }
        }

        return '0';
    }
}
if (!function_exists('hide_bfu_ids_for_tax_user'))
{
    function hide_bfu_ids_for_tax_user(): array
    {
//        $y = \DB::select(
//            "
//            SELECT
//                IFNULL(bfu.id,0) AS id
//            FROM
//                user_customers AS uc
//            LEFT JOIN bfu_invoices AS bfu ON bfu.user_customer_id = uc.id
//                AND bfu.invoice_status = 'done'
//                AND bfu.submitted_at > '2022-03-21 00:00:00'
//                AND bfu.submitted_at < '2023-03-21 00:00:00'
//                AND bfu.spent_irr_price < 66000000
//                AND bfu.user_customer_id NOT IN (SELECT user_customer_id FROM inventory_irr WHERE origin_type = 'in_deposit_direct_payment' AND submitted_at < '2023-03-21 00:00:00' AND applied_at IS NOT NULL and amount > 68000000  GROUP BY user_customer_id)
//            JOIN ( SELECT * FROM inventory_irr WHERE origin_type = 'in_deposit_online_payment' AND submitted_at < '2023-03-21 00:00:00' AND applied_at IS NOT NULL GROUP BY user_customer_id ) AS in_online ON in_online.user_customer_id = uc.id
//            "
//        );
        $y = \DB::select(
            "
            SELECT
                IFNULL(bfu.id,0) AS id
            FROM
                user_customers AS uc
            LEFT JOIN bfu_invoices AS bfu ON bfu.user_customer_id = uc.id 
                AND bfu.invoice_status = 'done' 
                AND bfu.submitted_at > '2022-03-21 00:00:00' 
                AND bfu.submitted_at < '2023-03-21 00:00:00'
                AND bfu.spent_irr_price < 96000000
                AND bfu.user_customer_id NOT IN (SELECT user_customer_id FROM inventory_irr WHERE origin_type = 'in_deposit_direct_payment' AND submitted_at < '2023-03-21 00:00:00' AND applied_at IS NOT NULL and amount > 98000000  GROUP BY user_customer_id) 
            JOIN ( SELECT * FROM inventory_irr WHERE origin_type = 'in_deposit_online_payment' AND submitted_at < '2023-03-21 00:00:00' AND applied_at IS NOT NULL GROUP BY user_customer_id ) AS in_online ON in_online.user_customer_id = uc.id 
            "
        );
        $c = collect($y)->pluck('id')->toArray();

        return \App\Models\BuyFromUs\BFU_Invoice::whereNotIn('id', $c)
            ->where('invoice_status', 'done')
            ->where('submitted_at', '>', '2022-03-21 00:00:00')
            ->where('submitted_at', '<', '2023-03-21 00:00:00')
            ->pluck('id')->toArray();
    }
}
if (!function_exists('hide_stu_ids_for_tax_user'))
{
    function hide_stu_ids_for_tax_user(): array
    {
        $y = \DB::select(
            "
            SELECT
                stu.id AS id
            FROM
                stu_invoices as stu
            WHERE stu.invoice_status = 'done' 
            and total_price_in_toman < 24500000
                AND stu.submitted_at > '2022-03-21 00:00:00' 
                AND stu.submitted_at < '2023-03-21 00:00:00' 
            "
        );
        $c = collect($y)->pluck('id')->toArray();

        return \App\Models\SellToUs\STU_Invoice::whereNotIn('id', $c)
            ->where('invoice_status', 'done')
            ->where('submitted_at', '>', '2022-03-21 00:00:00')
            ->where('submitted_at', '<', '2023-03-21 00:00:00')
            ->pluck('id')->toArray();

    }
}
if (!function_exists('get_system_config'))
{
    function get_system_config($parameter = false, $force = true)
    {
        if ($force || !session()->has('cache_system_configs'))
        {
            $last_sys_configs = \App\Models\SystemConfigs::notArchive()->last()->first();
            session()->put('cache_system_configs', $last_sys_configs);
        }
        else
        {
            $last_sys_configs = session('cache_system_configs')->se;
        }

        if ($parameter)
        {
            return $last_sys_configs->$parameter;
        }
        else
        {
            return $last_sys_configs;
        }
    }
}
if (!function_exists('is_active_recaptcha'))
{
    function is_active_recaptcha($section = 'public'): string
    {
        switch ($section)
        {
            case 'user_customer':
                $res = get_system_config('is_active_recaptcha_for_user_customer');
                break;
            case 'user_expert':
                $res = get_system_config('is_active_recaptcha_for_user_expert');
                break;
            case 'public':
            default:
                $res = get_system_config('is_active_recaptcha_for_public');
        }

        return $res === null ? '0' : $res;
    }
}
if (!function_exists('alert_customer_notification_system'))
{
    function alert_customer_notification_system()
    {
        $current_route = request()->route()->getName();
        $excepted_routes = [
            'auth.verify.email.index',
            'customer.authentication.verify_mobile.index',
            'customer.baned_page',
        ];
        if (in_array($current_route, $excepted_routes))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}
if (!function_exists('alert_customer_buy_and_sell'))
{
    function alert_customer_buy_and_sell()
    {
        $current_route = request()->route()->getName();
        $excepted_routes = [
            'customer.store.index',
        ];
        if (in_array($current_route, $excepted_routes))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
if (!function_exists('alert_customer_store_page'))
{
    function alert_customer_store_page()
    {
        $current_route = request()->route()->getName();
        $show_routes =
            [
                'customer.store.sell_to_us.index',
            ];
        if (in_array($current_route, $show_routes))
        {
            return true;
        }
        else
        {
            return false;
        }

    }
}
if (!function_exists('alert_expert_tax'))
{
    function alert_expert_tax(): bool
    {
        return true;
    }
}
if (!function_exists('alert_fronted_notification'))
{
    function alert_fronted_notification(): bool
    {
        return false;
        $current_route = request()->route()->getName();
        $show_routes =
            [
                'frontend.index',
                'auth.login.index',
                'customer.store.index',
            ];
        if (in_array($current_route, $show_routes))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
if (!function_exists('alert_auth_notification'))
{
    function alert_auth_notification(): bool
    {
        return true;
        $current_route = request()->route()->getName();
        $show_routes =
            [
                'frontend.index',
                'auth.login.index',
                'customer.store.index',
            ];
        if (in_array($current_route, $show_routes))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
if (!function_exists('message_alert'))
{
//    function message_alert()
//    {
//        return ' جهت سهولت  و کاهش کارمزد خرید کاربران سقف هر تراکنش  از درگاه پرداخت و پرداخت آنلاین به ۵۰ میلییون تومان ارتقا پیدا کرد';
//    }
//    function message_alert()
//    {
//        return 'به استحظار همراهان و مشتریان گرامی می رساند پاداش معرف در بازار های حرفه ای به مجموع پورسانت ها افزوده شد';
//    }
    function message_alert()
    {
        return 'ایردراپ رایگان دریافت کنید .';
    }
}
if (!function_exists('message_title'))
{

    function message_title()
    {
        return 'اطلاعیه مهم !';
    }
}
if (!function_exists('calc_dollar_avg_price'))
{
    function calc_dollar_avg_price()
    {
        $default_sell_to_us = $default_buy_from_us = 0;
        $default_money = \App\Models\Sys\SysDollarDefaultPrice::notArchive()->last()->first();

        if ($default_money)
        {
            $default_sell_to_us = $default_money->price_sell_to_us;
            $default_buy_from_us = $default_money->price_buy_from_us;
        }

        return ($default_sell_to_us + $default_buy_from_us) / 2;
    }
}
if (!function_exists('count_exist_job'))
{
    function count_exist_job($queue_name)
    {
        return \DB::table(config('queue.connections.database.table'))
            ->where('queue', $queue_name)->count();
    }
}
if (!function_exists('count_unanswered_messages'))
{
    function count_unanswered_messages()
    {
        $count_unanswered_messages = \App\Models\Page\ContactUsReceivedMessages::query()->whereNull('answer_at')->count();

        return ConvertNumbersEntoFa($count_unanswered_messages);
    }
}
if (!function_exists('IsMobileDev'))
{
    function IsMobileDev()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $user_ag = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/(Mobile|Android|Tablet|GoBrowser|[0-9]x[0-9]*|uZardWeb\/|Mini|Doris\/|Skyfire\/|iPhone|Fennec\/|Maemo|Iris\/|CLDC\-|Mobi\/)/uis', $user_ag))
            {
                return true;
            }
        }

        return false;
    }
}