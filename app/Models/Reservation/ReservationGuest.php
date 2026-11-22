<?php

namespace App\Models\Reservation;

use App\Models\User\UserCustomer;
use App\Models\User\UserGuest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationGuest extends Model
{
    protected $table = 'reservation_guests';

    public function user_guest()
    {
        return $this->belongsTo(UserGuest::class, 'user_guest_id');
    }
}