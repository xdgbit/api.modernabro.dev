<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    protected $table = 'about_us';

    protected $fillable = [
    'title_one',
    'text-one',
    'title_tow',
    'text_tow',
    'number_of_users',
     'number_of_cities',
    'number_of_accommodation',
    ];
}
