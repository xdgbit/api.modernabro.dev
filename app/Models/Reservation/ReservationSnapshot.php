<?php

namespace App\Models\Reservation;

use Illuminate\Database\Eloquent\Model;

class ReservationSnapshot extends Model
{
    protected $table = 'reservation_snapshots';
    protected $fillable = [
        'reservation_id',
        'version',
        'snapshot',
        'created_by',
    ];

    protected $casts = [
        'snapshot' => 'array',
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