<?php

namespace App\Notifications;

use Berkayk\OneSignal\OneSignalClient;
use Berkayk\OneSignal\OneSignalFacade;
use Berkayk\OneSignal\OneSignalServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageSent extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private array $data)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [OneSignalFacade::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toOneSignal(object $notifiable)
    { 
        $message_data=$this->data['messageData'];
    // return    OneSignal::sendNotificationToUser(
    //         "Some Message",
    //         // $userId,
    //         $url = null,
    //         $data = null,
    //         $buttons = null,
    //         $schedule = null
    //     );
    
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
