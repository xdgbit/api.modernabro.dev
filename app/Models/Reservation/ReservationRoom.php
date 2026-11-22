<?php

namespace App\Models\Reservation;

use App\Http\Helpers\Classes\LFM;
use App\Models\Accommodation\AccommodationUnit;
use App\Models\User\UserGuest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationRoom extends Model
{
    protected $appends = ['main_image'];
    protected $fillable = [
        'guest_count',
        'total_amount',
        'reservation_id',
        'extra_guest_count',
        'accommodation_unit_id',
    ];

    protected $casts = [
        'guest_count'  => 'integer',
        'total_amount' => 'decimal:2',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(
            Reservation::class
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationRoomItem::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            AccommodationUnit::class,
            'accommodation_unit_id'
        );
    }


    public function userGuests(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGuest::class,
            'reservation_guests',
            'reservation_room_id',
            'user_guest_id'
        );
    }

    public function getFaGuestCountAttribute()
    {
        return ConvertNumbersEntoFa(
            number_format($this->guest_count)
        );
    }

    public function getFaTotalAmountAttribute()
    {
        return ConvertNumbersEntoFa(
            number_format($this->total_amount)
        );
    }

    public function getMainImageAttribute()
    {
        $unit = $this->unit;
        if ($unit)
        {
            return $unit->main_image;
        }
        else
        {
            return null;
        }
    }
}