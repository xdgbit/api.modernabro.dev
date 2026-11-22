<?php

namespace App\Jobs\PusherNotifications;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BeamsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $interest;
    private $title;
    private $message;
    private $deep_link;

    /**
     * @param $interest
     * @param $title
     * @param $message
     * @param $deep_link
     */
    public function __construct($interest, $title, $message, $deep_link)
    {
        $this->interest = $interest;
        $this->title = $title;
        $this->message = $message;
        $this->deep_link = $deep_link;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */

    public function handle()
    {
        $beamsClient = new \Pusher\PushNotifications\PushNotifications([
            'instanceId' => config('beams.expert.instanceId'),
            'secretKey'  => config('beams.expert.secretKey'),
        ]);
        return $beamsClient->publishToInterests(
            $this->interest,
            [
                'web' => [
                    'notification' => [
                        'title'     => $this->title,
                        'body'      => $this->message,
                        'deep_link' => $this->deep_link,
                        'icon'      => asset('images/logo.png'),
                    ],
                ],
            ]
        );
    }

}
