<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ProjectCommunication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectCommunicationResolvedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ProjectCommunication $communication,
        private readonly User $resolver,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $title = $this->communication->status === ProjectCommunication::STATUS_APPROVED
            ? 'Solicitud aprobada'
            : 'Solicitud con cambios';

        return [
            'type' => $this->communication->status === ProjectCommunication::STATUS_APPROVED ? 'success' : 'warning',
            'title' => $title,
            'message' => $this->communication->response
                ? $this->communication->response
                : 'Tu solicitud fue revisada por ' . $this->resolver->name,
            'project_id' => $this->communication->project_id,
            'project_name' => $this->communication->project->name,
            'request_type' => $this->communication->request_type,
            'resolver_name' => $this->resolver->name,
            'communication_id' => $this->communication->id,
        ];
    }
}