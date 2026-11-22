<?php

namespace App\Models\Accommodation;

use App\Http\Helpers\Classes\LFM;
use App\Models\Reservation\ReservationRoom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccommodationUnit extends Model
{
    protected $table = 'accommodation_units';
    use SoftDeletes;

    protected $appends = [
        'main_image',
        'accommodation_city',
    ];
    protected $fillable = [
        'accommodation_id',
        'title',
        'code',
        'floor',
        'description',
        'status',
        'is_active',
        'base_price',
        'extra_price',
        'inventory',
        'guest_capacity',
        'extra_guest_capacity',
        'bathroom_count',
        'bedroom_count',
    ];

    protected $casts = [
        'floor'     => 'integer',
        'is_active' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(AccommodationUnitPrice::class, 'accommodation_unit_id', 'id');
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }
    public function getAccommodationCityAttribute()
    {
        $accommodation = $this->accommodation;
        if ($accommodation)
        {
            return $accommodation->city_title;
        }
        else
        {
            return null;
        }
    }

    public function files(): HasMany
    {
        return $this->hasMany(
            AccommodationUnitFile::class
        )->orderBy('sort');
    }

    public function getMainImageAttribute()
    {
        $file = $this->files()
            ->where('is_cover', true)
            ->first();

        return $file ? LFM::downloadLink($file->file_id, '404.jpg') : null;
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'accommodation_unit_amenity');
    }


    public function getStatusLabelAttribute()
    {
        switch ($this->status)
        {
            case 'available':
                return 'آزاد';
            case 'maintenance':
                return 'تعمیرات';
            case 'inactive':
                return 'غیرفعال';
            case 'cleaning':
                return 'در حال نظافت';
            default:
                return 'نامشخص';
        }
    }


    public function reservationRooms(): HasMany
    {
        return $this->hasMany(
            ReservationRoom::class,
            'accommodation_unit_id'
        );
    }


    public function getFaBasePriceAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->base_price));
    }

    public function getFaExtraPriceAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->extra_price));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithActivePrices($query)
    {
        return $query->with([
            'prices' => fn($query) => $query
                ->where('is_active', true)
                ->where(function ($query) {
                    $query
                        ->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query
                        ->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->orderBy('price'),
        ]);
    }

    public static function generate_code(): string
    {
        do
        {
            $code = 'UN-' . rand(10000, 99999);
        } while (self::where('code', $code)->exists());

        return $code;
    }
}