<?php

namespace App\Notifications;

use App\Messages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use NotificationChannels\AwsSns\SnsChannel;
use NotificationChannels\AwsSns\SnsMessage;
use App\User;

class MessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Messages $message, $subject)
    {
        $this->user = $user;
        $this->message = $message;
       // $this->fromAddress =$fromAddress;
        $this->subject =$subject;
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
       // return [SnsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
       // $url=URL::to('/message/?conversation_id=');
      //  $url = URL::to('https://testapp.i-visas.com/dashboard/messages/?conversation_id=');

        $url = URL::to('https://testapp.i-visas.com/admin_nav/admin_messages/?conversation_id=');

        return (new MailMessage)
        ->greeting('Hello Admin,')
        ->from('notify@i-visas.com')
        -> subject($this->subject)
        ->line('You have a new message kindly click the link below to access it.')
        ->action('Notification Action', url($url.$this->message->CONVERSATION_ID))
        ->line('Thank you for using our application!');

    }
    /*public function toSns($notifiable)
    {
        return SnsMessage::create()
        /*->greeting('Hello Admin,')
        ->from($this->fromAddress)
        -> subject($this->subject)
        ->line('You have a new message kindly click the link below to access it.')
        ->action('Notification Action', url('/'))
        ->line('Thank you for using our application!');

        ->body("Your {$notifiable->service} account was approved!")
        -> subject($this->subject)
        ->promotional()
        ->sender('MyBusiness');
    }*/

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
