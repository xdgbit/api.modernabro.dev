<?php

namespace App\Models\Accommodation;

use App\Http\Helpers\Classes\LFM;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationUnitFile extends Model
{
    protected $table = 'accommodation_unit_files';

    protected $fillable = [
        'accommodation_unit_id',
        'file_id',
        'is_cover',
        'sort',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'sort'     => 'integer',
    ];

    protected $appends = [
        'image_src',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            AccommodationUnit::class,
            'accommodation_unit_id'
        );
    }

    public function getImageSrcAttribute()
    {
        return LFM::downloadLink(
            $this->file_id,
            '404.jpg'
        );
    }
}