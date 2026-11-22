<?php

namespace App\Models\Accommodation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccommodationRule extends Model
{
    use SoftDeletes;

    protected $table = 'accommodation_rules';
    protected $guarded = [];
}