<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Expo\ExpoChannel;
use NotificationChannels\Expo\ExpoMessage;

class NewNotification extends Notification
{
    use Queueable;

    protected $notificationId;
    protected $title;
    protected $content;
    protected $data;

    public function __construct(array $notification)
    {
        $this->notificationId = $notification['id'] ?? null;
        $this->title = $notification['title'];
        $this->content = $notification['content'] ?? $notification['description'] ?? '';
        $this->data = $notification['data'] ?? [];
    }

    public function via($notifiable)
    {
        return [ExpoChannel::class];
    }

    public function toExpo($notifiable)
    {
        $message = ExpoMessage::create()
            ->title($this->title)
            ->body($this->content)
            ->badge(1)
            ->playSound()
            ->priority('high')
            ->channelId('default');

        // Build custom data array
        $customData = [];

        if ($this->notificationId) {
            $customData['url'] = 'app-skiut-2026://notifications/' . $this->notificationId; // TODO : fix this
            $customData['notificationId'] = $this->notificationId;
            $customData['type'] = 'notification';
        }

        // Merge with additional data
        if (!empty($this->data)) {
            $customData = array_merge($customData, $this->data);
        }

        // Set data using the correct method
        if (!empty($customData)) {
            $message->data($customData);
        }

        return $message;
    }
}
