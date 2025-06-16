<?php

namespace App\Contracts\Notifiable;

use App\Notifications\Messages\AfricasTalkingSmsMessage;

interface AfricasTalkingSmsNotification
{
    /**
     * Get the Africa's Talking / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \App\Notifications\Messages\AfricasTalkingSmsMessage
     */
    public function toAfricasTalking($notifiable): AfricasTalkingSmsMessage;

    /**
     * Get the SMS representation of the notification.
     * Alias for toAfricasTalking for backward compatibility.
     *
     * @param  mixed  $notifiable
     * @return \App\Notifications\Messages\AfricasTalkingSmsMessage
     */
    public function toSms($notifiable): AfricasTalkingSmsMessage;
}
