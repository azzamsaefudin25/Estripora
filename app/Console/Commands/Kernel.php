<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:kernel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }

    protected function schedule(Schedule $schedule): void
    {
    // Jalankan cleanup setiap 30 menit
    $schedule->command('transaksi:cleanup-expired')
             ->everyThirtyMinutes()
             ->withoutOverlapping();
    }
}
