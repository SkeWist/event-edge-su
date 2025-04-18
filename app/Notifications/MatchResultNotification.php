<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;

class MatchResultNotification extends Notification
{
    use Queueable;

    public $message;

    // Конструктор уведомления принимает сообщение
    public function __construct($message)
    {
        $this->message = $message;
    }

    // Метод для указания каналов уведомлений (база данных и почта)
    public function via($notifiable)
    {
        return ['database', 'mail']; // Уведомление через базу данных и почту
    }

    // Метод для отправки уведомлений в базу данных
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message, // Сохраняем сообщение в базе данных
        ];
    }

    // Метод для отправки уведомлений на почту
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Результат матча')
            ->line($this->message); // Сообщение, которое отправляется на почту
    }

    // Метод для отправки уведомлений через другие каналы (например, SMS)
    public function toSMS($notifiable)
    {
        return $this->message;
    }
}
