<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectCommunicationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Project $project,
        private readonly User $sender,
        private readonly string $messageText,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message' => $this->messageText,
            'type' => 'project_communication',
        ];
    }
}