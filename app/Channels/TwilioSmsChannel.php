<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use App\Notifications\Messages\TwilioSmsMessage;
use App\Contracts\Notifiable\TwilioSmsNotification as TwilioSmsNotificationContract;

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
     * @param  \Illuminate\Notifications\Notification|\App\Contracts\Notifiable\TwilioSmsNotification  $notification
     * @return void
     * @throws \RuntimeException If the notification doesn't implement required methods or returns invalid message type
     * 
     * The notification should implement one of the following methods:
     * - toTwilio($notifiable): TwilioSmsMessage
     * - toSms($notifiable): TwilioSmsMessage
     * 
     * It's recommended to implement the TwilioSmsNotification interface.
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('twilio', $notification)) {
            return;
        }

        // Get the message from the notification
        if (method_exists($notification, 'toTwilio')) {
            $message = $notification->toTwilio($notifiable);
        } elseif (method_exists($notification, 'toSms')) {
            $message = $notification->toSms($notifiable);
        } else {
            $notificationClass = get_class($notification);
            throw new \RuntimeException(
                "Notification [{$notificationClass}] is missing required method [toTwilio] or [toSms]"
            );
        }

        if (!($message instanceof \App\Notifications\Messages\TwilioSmsMessage)) {
            $messageType = is_object($message) ? get_class($message) : gettype($message);
            throw new \RuntimeException(
                "Notification must return a TwilioSmsMessage instance, got [{$messageType}]"
            );
        }

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
