<?php

namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;

class MarkQuotesExpired extends Command
{
    protected $signature   = 'quotes:expire';
    protected $description = 'Mark sent quotes past their valid_until date as expired';

    public function handle(): int
    {
        $count = Quote::query()
            ->where('status', 'envoye')
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', now()->toDateString())
            ->update(['status' => 'expire']);

        $this->info("Marked {$count} quote(s) as expired.");

        return self::SUCCESS;
    }
}
