<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
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
            'title'   => 'Tâche assignée',
            'message' => 'La tâche "' . $this->task->title . '" vous a été assignée.',
            'url'     => route('tasks.show', $this->task),
            'icon'    => 'bi-check2-square',
            'color'   => 'primary',
        ];
    }
}
