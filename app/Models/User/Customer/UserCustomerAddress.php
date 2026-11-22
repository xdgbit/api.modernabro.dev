<?php

namespace App\Models\User\Customer;

use App\Models\Location\City;
use App\Models\Location\Province;
use Illuminate\Database\Eloquent\Model;

class UserCustomerAddress extends Model
{
    protected $guarded = [];
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
