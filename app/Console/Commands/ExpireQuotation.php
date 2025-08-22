<?php

namespace App\Console\Commands;

use App\Models\Sale;
use Illuminate\Console\Command;

class ExpireQuotation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-quotation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire quotation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Sale::where('type', Sale::TYPE_QUO)->whereNot('status', Sale::STATUS_CONVERTED)->where('open_until', '<', now()->format('Y-m-d'))->update([
            'expired_at' => now()
        ]);
    }
}
