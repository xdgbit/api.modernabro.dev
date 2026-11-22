<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'province_id',
        'county_id',
        'district_id',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
