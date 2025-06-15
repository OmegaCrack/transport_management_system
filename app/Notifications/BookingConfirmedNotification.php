<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Messages\TwilioSmsMessage;
use App\Contracts\Notifiable\TwilioSmsNotification as TwilioSmsNotificationContract;

class BookingConfirmedNotification extends Notification implements ShouldQueue, TwilioSmsNotificationContract
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'twilio'];
    }



    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmation #' . $this->booking->id)
            ->line('Your booking has been confirmed!')
            ->line('Route: ' . $this->booking->route->origin . ' to ' . $this->booking->route->destination)
            ->line('Date: ' . $this->booking->departure_time->format('M d, Y H:i'))
            ->line('Passengers: ' . $this->booking->passengers)
            ->line('Total Fare: KES ' . number_format($this->booking->total_fare, 2))
            ->action('View Booking', url('/bookings/' . $this->booking->id))
            ->line('Thank you for choosing our service!');
    }

    /**
     * Get the Twilio / SMS representation of the notification.
     */
    public function toTwilio($notifiable): TwilioSmsMessage
    {
        $message = "Booking #{$this->booking->id} confirmed! " .
                 "Route: {$this->booking->route->origin} to {$this->booking->route->destination}. " .
                 "Date: {$this->booking->departure_time->format('M d, Y H:i')}. " .
                 "Passengers: {$this->booking->passengers}. " .
                 "Total: KES " . number_format($this->booking->total_fare, 2) . ". " .
                 "Thank you!";

        return (new TwilioSmsMessage())
            ->content($message);
    }
    
    /**
     * Get the SMS representation of the notification (alias for toTwilio).
     *
     * @param  mixed  $notifiable
     * @return \App\Notifications\Messages\TwilioSmsMessage
     */
    public function toSms($notifiable): TwilioSmsMessage
    {
        return $this->toTwilio($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'message' => 'Booking confirmed',
        ];
    }
}
