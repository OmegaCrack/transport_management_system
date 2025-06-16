<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Notifications\Messages\AfricasTalkingSmsMessage;

class AfricasTalkingSmsChannel
{
    /**
     * The Africa's Talking client instance.
     *
     * @var \AfricasTalking\SDK\AfricasTalking
     */
    protected $africasTalking;

    /**
     * The sender ID for the SMS.
     *
     * @var string
     */
    protected $senderId;

    /**
     * Create a new Africa's Talking channel instance.
     *
     * @param  \AfricasTalking\SDK\AfricasTalking  $africasTalking
     * @param  string  $senderId
     * @return void
     */
    public function __construct($africasTalking, $senderId)
    {
        $this->africasTalking = $africasTalking;
        $this->senderId = $senderId;
    }

    /**
     * Send the given notification.
     *
     * @template T of object{
     *     toAfricasTalking(mixed $notifiable): AfricasTalkingSmsMessage,
     *     toSms(mixed $notifiable): AfricasTalkingSmsMessage
     * }
     * 
     * @param  mixed  $notifiable
     * @param  T  $notification
     * @return void
     * @throws \RuntimeException If the notification doesn't implement required methods or returns invalid message type
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('africas_talking', $notification)) {
            return;
        }

        // Get the message from the notification
        if (method_exists($notification, 'toAfricasTalking')) {
            $message = $notification->toAfricasTalking($notifiable);
        } elseif (method_exists($notification, 'toSms')) {
            $message = $notification->toSms($notifiable);
        } else {
            $notificationClass = get_class($notification);
            throw new \RuntimeException(
                "Notification [{$notificationClass}] is missing required method [toAfricasTalking] or [toSms]"
            );
        }

        if (!($message instanceof AfricasTalkingSmsMessage)) {
            $messageType = is_object($message) ? get_class($message) : gettype($message);
            throw new \RuntimeException(
                "Notification must return an AfricasTalkingSmsMessage instance, got [{$messageType}]"
            );
        }

        try {
            $sms = $this->africasTalking->sms();
            
            $response = $sms->send([
                'to'      => $to,
                'message' => trim($message->content),
                'from'    => $this->senderId
            ]);
            
            Log::info('Africa\'s Talking SMS sent', [
                'to' => $to,
                'response' => $response
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send Africa\'s Talking SMS: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
