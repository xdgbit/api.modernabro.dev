<?php

namespace App\Models\Reservation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Accommodation\AccommodationUnitPrice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationRoomItem extends Model
{
    protected $table = 'reservation_room_items';
    protected $fillable = [
        'reservation_room_id',
        'accommodation_unit_price_id',
        'date',
        'unit_price',
        'extra_guest_price',
        'guest_count',
        'extra_guest_count',
        'total_amount',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function reservation(): BelongsTo
    {
        return $this->hasOneThrough(
            Reservation::class,
            ReservationRoom::class,
            'id',
            'id',
            'reservation_room_id',
            'reservation_id'
        );
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ReservationRoom::class, 'reservation_room_id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(
            AccommodationUnitPrice::class,
            'accommodation_unit_price_id'
        );
    }

    public function histories(): HasMany
    {
        return $this->hasMany(
            ReservationHistory::class
        )->latest('changed_at');
    }

}