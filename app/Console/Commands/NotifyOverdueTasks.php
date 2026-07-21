<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyOverdueTasks extends Command
{
    protected $signature = 'tasks:notify-overdue';
    protected $description = 'Identifie les tâches en retard et notifie les employés assignés (ou les admins à défaut).';

    public function handle()
    {
        $overdueTasks = Task::with('employees')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['termine', 'annule'])
            ->get();

        $this->info("Analyse de " . $overdueTasks->count() . " tâche(s) en retard...");

        foreach ($overdueTasks as $task) {
            $emails = $task->employees->pluck('email')->filter()->toArray();

            $recipients = $emails
                ? User::whereIn('email', $emails)->where('company_id', $task->company_id)->get()
                : User::role('admin')->where('company_id', $task->company_id)->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new TaskOverdue($task));
                $this->line("Notification envoyée pour la tâche : {$task->title}");
            }
        }

        $this->info("Traitement terminé.");
    }
}
