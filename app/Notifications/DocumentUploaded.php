<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentUploaded extends Notification
{
    use Queueable;

    public function __construct(public Document $document) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $projectName = $this->document->project ? $this->document->project->name : 'Général';
        
        return [
            'title'   => 'Nouveau document',
            'message' => 'Un document "' . $this->document->original_name . '" a été ajouté au projet : ' . $projectName,
            'url'     => route('documents.index', ['project_id' => $this->document->project_id]),
            'icon'    => 'bi-file-earmark-text',
            'color'   => 'info',
        ];
    }
}
