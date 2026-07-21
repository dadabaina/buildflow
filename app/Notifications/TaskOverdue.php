<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskOverdue extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Tâche en retard',
            'message' => 'La tâche "' . $this->task->title . '" est en retard depuis le ' . $this->task->due_date?->format('d/m/Y') . '.',
            'url'     => route('tasks.show', $this->task),
            'icon'    => 'bi-exclamation-triangle',
            'color'   => 'danger',
        ];
    }
}
