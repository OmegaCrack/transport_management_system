<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Notifications\Messages\TwilioSmsMessage;

class TwilioSmsChannel
{
    /**
     * The Twilio client instance.
     *
     * @var \Twilio\Rest\Client
     */
    protected $twilio;

    /**
     * The phone number notifications should be sent from.
     *
     * @var string
     */
    protected $from;

    /**
     * Create a new Twilio channel instance.
     *
     * @param  \Twilio\Rest\Client  $twilio
     * @param  string  $from
     * @return void
     */
    public function __construct(Client $twilio, $from)
    {
        $this->from = $from;
        $this->twilio = $twilio;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('twilio', $notification)) {
            return;
        }

        // Dynamically call the appropriate method on the notification
        $message = method_exists($notification, 'toTwilio')
            ? $notification->toTwilio($notifiable)
            : $notification->toSms($notifiable);

        try {
            $this->twilio->messages->create($to, [
                'from' => $this->from,
                'body' => trim($message->content),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Twilio SMS: ' . $e->getMessage());
            throw $e;
        }
    }
}
