<?php

use App\Console\Commands\MarkQuotesExpired;
use App\Console\Commands\NotifyOverdueInvoices;
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
