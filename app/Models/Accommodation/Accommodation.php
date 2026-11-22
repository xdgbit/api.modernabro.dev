<?php

namespace App\Models\Accommodation;

use App\Http\Helpers\Classes\LFM;
use App\Models\Location\City;
use App\Models\Reservation\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Accommodation extends Model
{
    protected $appends = [
        'rating_score',
        'rating_label',
        'reviews_count',
        'booking_type_policy',
    ];
    protected $fillable = [
        'slug',
        'type',
        'title',
        'is_active',
        'description',
        'star_rating',
    ];

    protected function casts(): array
    {
        return [
            'star_rating' => 'integer',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(AccommodationReview::class);
    }

    public function getDisplayRatingAttribute(): ?float
    {
        if ($this->star_rating > 0)
        {
            return (float)$this->star_rating;
        }

        return $this->rating_score;
    }

    public function getRatingScoreAttribute(): ?float
    {
        $avg = $this->relationLoaded('reviews')
            ? $this->reviews->avg('rating')
            : $this->reviews()->avg('rating');

        return $avg !== null ? round((float)$avg, 1) : null;
    }

    public function getReviewsCountAttribute(): int
    {
        // اگر ریلیشن reviews لود شده باشد از همان استفاده می‌کند تا کوئری اضافه نزند
        return $this->relationLoaded('reviews')
            ? $this->reviews->count()
            : $this->reviews()->count();
    }


    public function getRatingLabelAttribute(): ?string
    {
        $score = $this->rating_score;

        if ($score === null)
        {
            return null;
        }

        return match (true)
        {
            $score >= 4.5 => 'عالی',
            $score >= 4.0 => 'خیلی‌خوب',
            $score >= 3.0 => 'خوب',
            $score >= 2.0 => 'متوسط',
            default => 'ضعیف',
        };
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function canBeActivated(): bool
    {
        return $this->policy()->exists()
            && $this->units()->exists();
    }

    public function rules()
    {
        return $this->hasMany(AccommodationRule::class);
    }

    public function policy(): HasOne
    {
        return $this->hasOne(AccommodationPolicy::class);
    }

    public function getBookingTypePolicyAttribute()
    {
        $policy = $this->policy;
        if ($policy)
        {
            return $policy->booking_type_policy;
        }
        else
        {
            return null;
        }
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'accommodation_amenity');
    }

    public function files()
    {
        return $this->hasMany(AccommodationFile::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(
            Reservation::class
        );
    }

    public function units(): HasMany
    {
        return $this->hasMany(AccommodationUnit::class);
    }

    public function getCoverImageAttribute()
    {
        $file = $this->files()
            ->where('is_cover', true)
            ->first();

        return $file ? LFM::downloadLink($file->file_id, '404.jpg') : null;
    }

    public function getCityTitleAttribute()
    {
        $city = $this->city;
        if ($city)
        {
            return $city->title;
        }
        else
        {
            return '';
        }
    }

    public function getFaTypeAttribute()
    {
        switch ($this->type)
        {
            case 'hotel':
                return 'هتل';
            case 'villa':
                return 'ویلا';
            case 'suite':
                return 'سوئیت';
            case 'cottage':
                return 'کلبه';
            default:
                return '__!!!!__';
        }
    }

    public function getFaFloorCountAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->floor_count));
    }

    public function getFaUnitsCountAttribute()
    {
        return ConvertNumbersEntoFa(number_format($this->units_count));
    }

    public function getUnitsCountAttribute()
    {
        return $this->units()->count();
    }
}