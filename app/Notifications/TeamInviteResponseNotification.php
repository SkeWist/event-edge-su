<?php
namespace App\Notifications;

use App\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TeamInviteResponseNotification extends Notification
{
    use Queueable;

    protected $invite;
    protected $message;

    public function __construct(TeamInvite $invite, $message)
    {
        $this->invite = $invite;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database']; // Уведомление будет отправляться в базу данных
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'invite_id' => $this->invite->id,
            'team_id' => $this->invite->team_id,
            'user_id' => $this->invite->user_id,
        ];
    }
}
