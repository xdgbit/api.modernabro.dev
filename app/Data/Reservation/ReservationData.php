<?php

namespace App\Data\Reservation;

use Carbon\Carbon;

final class ReservationData
{
    /**
     * @param array<int, array<string, mixed>> $rooms
     */
    public function __construct(
        public int           $customerId,
        public Carbon|string $checkIn,
        public Carbon|string $checkOut,
        public array         $rooms,
        public ?string       $description = null,
        public ?int          $submittedBy = null,
    )
    {
    }

    public function checkInDate(): Carbon
    {
        return $this->checkIn instanceof Carbon
            ? $this->checkIn->copy()
            : Carbon::parse($this->checkIn);
    }

    public function checkOutDate(): Carbon
    {
        return $this->checkOut instanceof Carbon
            ? $this->checkOut->copy()
            : Carbon::parse($this->checkOut);
    }
}