<?php

namespace App\Models\Reservation;

use Illuminate\Database\Eloquent\Model;

class ReservationHistory extends Model
{
    protected $fillable = [

        'reservation_id',

        'from_status',

        'to_status',

        'description',

        'changed_by',

        'changed_at',

    ];


    protected $casts = [

        'changed_at' => 'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Reservation
    |--------------------------------------------------------------------------
    */

    public function reservation()
    {
        return $this->belongsTo(
            Reservation::class
        );
    }
}