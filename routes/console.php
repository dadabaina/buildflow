<?php

use App\Console\Commands\MarkQuotesExpired;
use App\Console\Commands\NotifyOverdueInvoices;
use App\Console\Commands\NotifyOverdueTasks;
use App\Console\Commands\SendNotificationDigests;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// US-09-02 — expire quotes daily
Schedule::command(MarkQuotesExpired::class)->dailyAt('02:00');

// Notify overdue invoices daily
Schedule::command(NotifyOverdueInvoices::class)->dailyAt('03:00');

// Notify overdue tasks daily
Schedule::command(NotifyOverdueTasks::class)->dailyAt('07:00');

// Envoi des digests email (récap quotidien groupé par type de notification, une fois par jour)
// Placé en fin de journée pour couvrir tous les événements générés depuis le matin.
Schedule::command(SendNotificationDigests::class)->dailyAt('18:00');
