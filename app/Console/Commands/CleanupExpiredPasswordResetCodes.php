<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PasswordResetCode;

class CleanupExpiredPasswordResetCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password-reset:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired password reset verification codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletedCount = PasswordResetCode::cleanupExpired();
        
        $this->info("Cleaned up {$deletedCount} expired password reset codes.");
        
        return Command::SUCCESS;
    }
}
