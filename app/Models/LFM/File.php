<?php

namespace App\Models\LFM;

use App\Models\User\Expert\UserExpert;
use App\Models\User\Customer\UserCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class File extends Model
{
    use SoftDeletes;

    protected $table = 'lfm_files';
    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $changes = $model->getChanges();
            if (is_array($changes) && count($changes) === 0)
            {
                if (is_null($model->created_by))
                {
                    if (auth()->guard('user_expert')->check())
                    {
                        $model->created_by = auth()->guard('user_expert')->id();
                    }
                    else
                    {
                        if (auth()->guard('user_customer')->check())
                        {
                            $model->created_by = -1;
                        }
                        else
                        {
                            $model->created_by = 0;
                        }
                    }
                }
            }
        });
    }

    private function human_readable_file_size($bytes)
    {
        $result = "";
        $bytes = floatval($bytes);
        $arBytes =
            [
                0 => ["UNIT" => "TB", "VALUE" => pow(1024, 4)],
                1 => ["UNIT" => "GB", "VALUE" => pow(1024, 3)],
                2 => ["UNIT" => "MB", "VALUE" => pow(1024, 2)],
                3 => ["UNIT" => "KB", "VALUE" => 1024],
                4 => ["UNIT" => "B", "VALUE" => 1]
            ];

        foreach ($arBytes as $arItem)
        {
            if ($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 1))) . " " . $arItem["UNIT"];
                break;
            }
        }

        return $result;
    }

    public function file_mime_type()
    {
        return $this->belongsTo(FileMimeType::class, 'file_mime_type_id', 'id');
    }

    public function user_customer()
    {
        return $this->belongsTo(UserCustomer::class, 'user_id')->where('user_type', 'user_customer');
    }

    public function user_expert()
    {
        return $this->belongsTo(UserExpert::class, 'user_id')->where('user_type', 'user_expert');
    }

    public function getHumanSizeAttribute()
    {
        return $this->human_readable_file_size($this->size);
    }

    public function getFullStorePathAttribute()
    {
        return $this->store_path . $this->store_filename;
    }

    public function getDiskAttribute()
    {
        return \App\Http\Helpers\Classes\LFM::getStoreDisk($this->upload_reason);
    }

    public function getFileContentAttribute()
    {
        if (\Storage::disk($this->disk)->exists($this->full_store_path))
        {
            return \Storage::disk($this->disk)->get($this->full_store_path);
        }
        else
        {
            return false;
        }
    }

    public function getBase64SrcAttribute()
    {
        return 'data:image/' . 'jpg' . ';base64,' . base64_encode($this->file_content);
    }

    public function getFullOriginalNameAttribute()
    {
        return $this->original_name . '.' . $this->original_extension;
    }

    public function getRealEXTAttribute()
    {
        $ext = 'txt';
        $file_mime_type = $this->file_mime_type;
        if ($file_mime_type)
        {
            if ($file_mime_type->ext)
            {
                $ext = $this->file_mime_type->ext;
            }
        }

        return $ext;
    }

    public function getFullGeneratedNameAttribute()
    {
        $ext = $this->real_ext;
        $md5_hash = $this->md5_hash;

        return $md5_hash . '.' . $ext;
    }

    public function getFullAbsolutePathAttribute()
    {
        return \Storage::disk($this->disk)->path($this->full_store_path);
    }

    public function getPublicLinkAttribute()
    {
        return \Storage::disk($this->disk)->url($this->full_store_path);
    }

    public function getIsPermittedAttribute()
    {
        $is_user_expert = auth()->guard('user_expert')->check();

        if ($is_user_expert)
        {
            return true;
        }
        else
        {
            $is_user_customer = auth()->guard('user_customer')->check();

            switch ($this->upload_reason)
            {
                case 'sys_digital_currency_img':
                case 'term_and_conditions_img':
                case 'about_us_img':
                case 'article_middle_img':
                case 'article_header_img':
                case 'article_content_img':
                case 'payment_method_content_img':
                case 'notification_content_img':
                    return true;
                case 'user_customer_profile':
                    if ($is_user_customer)
                    {
                        if ($this->user_id === auth()->guard('user_customer')->id())
                        {
                            return true;
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
                case 'user_expert_profile':
                case 'user_customer_authentication_selfie_photo':
                case 'user_customer_authentication_selfie_video':
                case 'user_customer_accepted_rules_selfie_video':
                case 'user_customer_deposit_direct_selfie_video':
                case 'user_customer_authentication_mellicard':
                case 'user_customer_authentication_bank_account_card':
                case 'user_customer_bfu_card_to_card_document':
                default:
                    return false;
            }
        }
    }

    public function getIsPublicAttribute()
    {
        $disk = $this->disk;
        $file_system = config('filesystems.disks.' . $disk);
        if ($file_system)
        {
            if (isset($file_system['visibility']))
            {
                if ($file_system['visibility'] === 'public')
                {
                    return true;
                }
            }
        }

        return false;
    }

}
