<?php

namespace App\Notifications;

use NotificationChannels\AwsSns\SnsChannel;
use NotificationChannels\AwsSns\SnsMessage;
use Illuminate\Notifications\Notification;

class AWSNS extends Notification
{
    public function via($notifiable)
    {
        return [SnsChannel::class];
    }

    public function toSns($notifiable)
    {
        // You can just return a plain string:
        return "Your {$notifiable->service} account was approved!";
        
        // OR explicitly return a SnsMessage object passing the message body:
        return new SnsMessage("Your {$notifiable->service} account was approved!");
        
        // OR return a SnsMessage passing the arguments via `create()` or `__construct()`:
        return SnsMessage::create([
            'body' => "Your {$notifiable->service} account was approved!",
            'transactional' => true,
            'sender' => 'MyBusiness',
        ]);

        // OR create the object with or without arguments and then use the fluent API:
        return SnsMessage::create()
            ->body("Your {$notifiable->service} account was approved!")
            ->promotional()
            ->sender('MyBusiness');
    }
}
