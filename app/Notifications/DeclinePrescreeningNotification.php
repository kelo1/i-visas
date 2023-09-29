<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use App\Client;
use App\Messages;

class DeclinePrescreeningNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Client $client, Messages $message, $client_name, $toAddress)
    {
        $this->client = $client;
        $this->client_name =$client_name;
        $this->message = $message;
        $this->toAddress = $toAddress;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //$url=URL::to('/message/?conversation_id=');
        $url = URL::to('https://testapp.i-visas.com/dashboard/messages/?conversation_id=');

        return (new MailMessage)
            ->greeting('Hello '.$this->client_name.',')
            ->from('admin@i-visas.com')
            ->replyTo($this->toAddress)
            ->subject('Pre-Screening Declined')
            ->line('You have a new message kindly click the link below to access it.')
            ->action('Notification Action', url($url.$this->message->CONVERSATION_ID))
            ->line('Thank you for using our application!');
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
