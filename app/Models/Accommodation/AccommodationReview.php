<?php

namespace App\Models\Accommodation;


use App\Models\User\UserCustomer;
use Illuminate\Database\Eloquent\Model;

class AccommodationReview extends Model
{
    protected $table = 'accommodation_reviews';
    protected $guarded = [];

    public function user_customer()
    {
        return $this->belongsTo(UserCustomer::class, 'user_customer_id', 'id');
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function getJalaliSubmittedAtAttribute()
    {
        return $this->submitted_at ? ConvertNumbersEntoFa(verta($this->submitted_at) . '') : null;
    }

    public function getAuthorFullNameAttribute()
    {
        $user_customer = $this->user_customer;
        if ($user_customer)
        {
            return $user_customer->full_name;
        }
        else
        {
            return null;
        }
    }
}