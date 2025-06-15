<?php

namespace App\Contracts\Notifiable;

use App\Notifications\Messages\TwilioSmsMessage;

interface TwilioSmsNotification
{
    /**
     * Get the Twilio SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \App\Notifications\Messages\TwilioSmsMessage
     */
    public function toTwilio($notifiable): TwilioSmsMessage;

    /**
     * Get the SMS representation of the notification.
     * This is an alias for toTwilio for backward compatibility.
     *
     * @param  mixed  $notifiable
     * @return \App\Notifications\Messages\TwilioSmsMessage
     */
    public function toSms($notifiable): TwilioSmsMessage;
}
