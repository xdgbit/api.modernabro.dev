<?php

namespace App\Models\Sys\PaymentMethod;

use App\Models\User\Expert\UserExpert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysDirectPaymentConfig extends Model
{
    use SoftDeletes;

    protected $table = 'sys_direct_payment_configs';

    public function submitter()
    {
        return $this->belongsTo(UserExpert::class, 'submitted_by', 'id');
    }

    public function visits()
    {
        return $this->hasMany(SysPaymentConfigVisit::class, 'sys_payment_config_id', 'id');
    }

    public function scopeNotArchive($q)
    {
        return $q->whereNull('archived_at');
    }

    public function scopeLast($q)
    {
        return $q->orderBy('id', 'Desc');
    }

    public function getFaLimitMinPriceAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->limit_min_price));
    }

    public function getFaLimitMaxPriceAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->limit_max_price));
    }
    public function getFaMaxWageTaxAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->max_wage_tax));
    }
    public function getFaPercentWageTaxAttribute()
    {
        return ConvertNumbersEntoFa(custom_number_format($this->percent_wage_tax));
    }

    public function getJalaliSubmittedAtAttribute()
    {
        return $this->submitted_at ? ConvertNumbersEntoFa(verta($this->submitted_at) . '') : null;
    }
    public function getFaPaymentMethodStatusAttribute()
    {
        switch ($this->payment_method_status)
        {
            case '1':
                return 'فعال';
            case '0':
                return 'غیرفعال';
            default:
                return '_!!!_';
        }
    }
    public function getSubmitterFullNameAttribute()
    {
        if ($this->submitter)
        {
            return $this->submitter->full_name;
        }
        else
        {
            return '';
        }
    }

    public function getFaNotificationTypeAttribute()
    {
        switch ($this->notification_type)
        {
            case 'popup':
                return 'پنجره بازشو';
            case 'alert':
                return 'هشدار';
            case 'both':
                return 'هردو';
            default:
                return '_!!!_';
        }
    }

    public function getFaNotificationColorAttribute()
    {
        switch ($this->notification_color)
        {
            case 'info':
                return 'info';
            case 'success':
                return 'success';
            case 'danger':
                return 'danger';
            case 'warning':
                return 'warning';
            default:
                return '_!!!_';
        }
    }

    public function getNotArchiveIconAttribute()
    {
        return !$this->archived_at ? '<span class="fa fa-star">' : '';
    }


    public function countVisit($user_id)
    {
        return $this->visits()->where('user_customer_id', $user_id)->count();
    }

    public function getShowPopupForAuthAttribute()
    {
        if ($this->notification_count_show)
        {
            if ($this->notification_count_show != 'all')
            {
                $count = $this->countVisit(auth()->guard('user_customer')->id());
                if ($count < (int)$this->notification_count_show)
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
                return true;
            }
        }
        else
        {
            return false;
        }
    }

    public function getFaNotificationCountShowAttribute()
    {
        if ($this->notification_count_show)
        {
            if($this->notification_count_show==='all')
            {
                return 'همیشه';
            }
            else return ConvertNumbersEntoFa($this->notification_count_show);
        }
        else
        {
            return '_!!!_';
        }
    }
    public function getFaMaxSuccessPaymentPerDayAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->max_success_payment_per_day));
    }


}
