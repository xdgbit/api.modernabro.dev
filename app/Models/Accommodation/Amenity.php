<?php

namespace App\Models\Accommodation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    protected $fillable = [
        'title',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(
            Accommodation::class,
            'accommodation_amenity'
        );
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(AccommodationUnit::class, 'accommodation_unit_amenity');
    }
}