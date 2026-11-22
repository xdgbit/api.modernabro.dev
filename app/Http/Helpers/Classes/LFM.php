<?php

namespace App\Http\Helpers\Classes;


use Carbon\Carbon;
use App\Models\LFM\File;
use App\Models\LFM\FileMimeType;

class LFM
{
    public static function strSanitize($string, $force_lowercase = true, $anal = false)
    {
        $strip = ["~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;", "â€”", "â€“", ",", "<", ".", ">", "/", "?"];
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;

        return ($force_lowercase) ? (function_exists('mb_strtolower')) ? mb_strtolower($clean, 'UTF-8') : strtolower($clean) : $clean;
    }

    public static function getStorePath($upload_reason)
    {
        switch ($upload_reason)
        {
            case 'product_img':
                return '/products/';
            case 'article_img':
                return '/article/';
            default:
                return '/default/';
        }
    }

    public static function getStoreDisk($upload_reason)
    {
        switch ($upload_reason)
        {
            case 'private':
                return 'private';
            default:
                return 'public';
        }
    }

    public static function getMimeType($file)
    {
        return FileMimeType::where('mime_type', $file->getMimeType())->first();
    }

    public static function prepareOriginalFileName($file, $file_mime_type)
    {
        $extension = $file_mime_type->ext;
        $client_original_name = $file->getClientOriginalName();
        $original_name_without_ext = substr($client_original_name, 0, strlen($client_original_name) - strlen($extension) - 1);

        return self::strSanitize($original_name_without_ext);
    }

    public static function prepareOriginalExtension($file, $file_mime_type)
    {
        $extension = $file_mime_type->ext;
        $client_original_name = $file->getClientOriginalName();
        $original_name_without_ext = substr($client_original_name, strlen($client_original_name) - strlen($extension) - 1, strlen($client_original_name));

        return self::strSanitize($original_name_without_ext);
    }

    public static function fileUpload($file, $upload_reason, $user_type = 'user_customer', $user_id = false)
    {
        $file_mime_type = self::getMimeType($file);
        if ($file_mime_type)
        {
            if ($user_id === false)
            {
                $user_id = auth()->guard($user_type)->id();
            }
            else
            {
                $user_id = 0;
            }
            $store_path = self::getStorePath($upload_reason);
//            $store_disk = self::getStoreDisk($upload_reason);
            $store_disk = 'public';

            $new_file = new File;
            $new_file->user_id = $user_id;
            $new_file->user_type = $user_type;
            $new_file->file_mime_type_id = $file_mime_type->id;
            $new_file->file_size = $file->getSize();
            $new_file->original_name = self::prepareOriginalFileName($file, $file_mime_type);
            $new_file->original_extension = self::prepareOriginalExtension($file, $file_mime_type);
            $new_file->original_mime_type = $file->getMimeType();
            $new_file->store_path = $store_path;
            $new_file->store_filename = '';
            $new_file->upload_reason = $upload_reason;
            $new_file->md5_hash = md5_file($file);
            $new_file->save();

            $store_filename = 'fid_' . $new_file->id . '_ut_' . $user_type . '_uid_' . $user_id . '_' . md5_file($file) . "_" . Carbon::now()->format('Y_m_d') . '_' . $file_mime_type->ext;

            $new_file->store_filename = $store_filename;
            $new_file->save();

            $upload_result = \Storage::disk($store_disk)->put($store_path . $store_filename, $file->getContent());

            if ($upload_result)
            {
                return $new_file;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public static function downloadLink($file_id, $default = false)
    {
        if ($file_id)
        {
            $file = \App\Models\LFM\File::find($file_id);
            if ($file)
            {
                $is_public = $file->is_public;
                $file_content = $file->file_content;
                if ($file_content)
                {
                    if ($is_public)
                    {
                        $public_link = $file->public_link;
                        if ($public_link)
                        {
                            return $public_link;
                        }
                    }

                    return route('frontend.lfm.bfu.download_by_id', ['encoded_file_id' => sys_encode_id($file_id), 'default' => $default]);
                }
            }
        }
        $defaultPath = public_path($default);

        if ($default && file_exists($defaultPath))
        {
            return asset($default);
        }

        return asset('system/not_found_pictures/404.jpg');
    }

    public static function get_default_picture($file_name)
    {
        if (\Storage::disk('system_not_found_pictures')->exists($file_name))
        {
            return \Storage::disk('system_not_found_pictures')->path($file_name);
        }
        else
        {
            return false;
        }
    }

}