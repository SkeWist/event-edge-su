<?php

namespace App\Notifications;

use App\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInviteNotification extends Notification
{
    use Queueable;

    protected $invite;
    protected $message;

    public function __construct(TeamInvite $invite, $message)
    {
        $this->invite = $invite;
        $this->message = $message;  // Проверяем, что значение message правильно сохраняется
    }

    // Указываем, какие каналы уведомления будут использоваться
    public function via($notifiable)
    {
        return ['database']; // Уведомление будет отправляться в базу данных
    }

    // Этот метод будет использоваться для отправки уведомлений в базу данных
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message, // Передаем дефолтное или кастомное сообщение
            'invite_id' => $this->invite->id,
            'team_id' => $this->invite->team_id,
            'user_id' => $this->invite->user_id,
        ];
    }

    // Можно добавить метод для отправки уведомлений через другие каналы, например, через email, если нужно.
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line($this->message)
            ->action('Принять приглашение', url('/'))
            ->line('Спасибо за использование нашего приложения!');
    }
}
