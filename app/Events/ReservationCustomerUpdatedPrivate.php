<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;


class ReservationCustomerUpdatedPrivate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $msg;
    private $user_customer;

    /**
     * Create a new event instance.
     *
     * @param $user_customer
     * @param bool|array $msg
     */
    public function __construct($user_customer, $msg = false)
    {
        $this->msg = $msg ?: [
            'type'    => 'info',
            'title'   => 'Reservation Updated',
            'message' => 'Your reservation information has been updated.',
        ];

        $this->user_customer = $user_customer;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {

        return [check_production_channel($this->user_customer->my_private_pusher_channel)];
    }

    public function broadcastAs()
    {
        return 'ReservationCustomerUpdated_Private';
    }

    public function broadcastQueue()
    {
        return 'push_event_reservation_updated';
    }
}


