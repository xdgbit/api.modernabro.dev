<?php

namespace App\Models\User\Customer;

use App\Models\Reservation\Reservation;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserCustomer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'user_customers';
    protected $appends = [
        'full_name',
        'has_password',
    ];
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_active',
        'mobile_number',
        'email_verified_at',
        'mobile_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'mobile_verified_at' => 'datetime',
        'email_verified_at'  => 'datetime',
        'last_login_at'      => 'datetime',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserCustomerAddress::class);
    }

    public function verify_mobile_codes()
    {
        return $this->hasMany(UserCustomerVerifyMobileCode::class, 'user_customer_id', 'id');
    }

    public function getVerifyMobileAvailableRequestsAttribute()
    {
        $limit_per_day = 5;
        $today_sent_counts = $this->verify_mobile_codes()->whereDate('created_at', \Carbon\Carbon::today())->count();
        if ($today_sent_counts >= $limit_per_day)
        {
            return 0;
        }
        else
        {
            return $limit_per_day - $today_sent_counts;
        }
    }


    public function getVerifyMobilePendingTimeToNextRequestAttribute()
    {
        $pending_time_between_request = 60;
        $last_sent_sms = $this->verify_mobile_codes()->orderBy('id', 'DESC')->first();

        if ($last_sent_sms)
        {
            $diff_to_now = \Carbon\Carbon::now()->diffInSeconds($last_sent_sms->created_at, true);
            $diff_to_now = (int)$diff_to_now;
            if ($diff_to_now > 0)
            {
                if ($diff_to_now > $pending_time_between_request)
                {
                    return 0;
                }
                else
                {
                    return $pending_time_between_request - $diff_to_now;
                }
            }
            else
            {
                return $pending_time_between_request;
            }
        }
        else
        {
            return 0;
        }
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getHasPasswordAttribute(): bool
    {
        return !empty($this->attributes['password']);
    }

    public function getMyPrivatePusherChannelAttribute()
    {
        $my_channel = $this->private_pusher_channel;
        $my_channel_last_update = $this->private_pusher_channel_last_update;
        if (is_null($my_channel))
        {
            $this->private_pusher_channel = 'user_private_channel_' . substr(md5($this->id) . md5(rand(100000, 1000000000)), 10, 30);
            $this->private_pusher_channel_last_update = now();
            $this->save();

            return $this->private_pusher_channel;
        }
        elseif ((time() - strtotime($my_channel_last_update)) > 86400)
        {
            $this->private_pusher_channel = 'user_private_channel_' . substr(md5($this->id) . md5(rand(100000, 1000000000)), 10, 30);
            $this->private_pusher_channel_last_update = now();
            $this->save();

            return $this->private_pusher_channel;
        }
        else
        {
            return $this->private_pusher_channel;
        }
    }

}