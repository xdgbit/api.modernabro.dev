<?php


namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
