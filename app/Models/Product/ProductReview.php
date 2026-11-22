<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\Customer\UserCustomer;

class ProductReview extends Model
{
    protected $table = 'product_reviews';
    protected $guarded = [];

    public function user_customer()
    {
        return $this->belongsTo(UserCustomer::class, 'user_customer_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
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