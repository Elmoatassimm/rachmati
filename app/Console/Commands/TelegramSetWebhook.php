<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Validator;

class TelegramSetWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook 
                            {url? : The webhook URL to set}
                            {--remove : Remove the webhook instead of setting it}
                            {--info : Show current webhook information}
                            {--test : Test bot connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Telegram bot webhook configuration';

    private TelegramService $telegramService;

    /**
     * Create a new command instance.
     */
    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🤖 Telegram Bot Webhook Management');
        $this->line('');

        // Show current bot configuration
        $this->showBotConfig();

        // Handle different options
        if ($this->option('test')) {
            return $this->testConnection();
        }

        if ($this->option('info')) {
            return $this->showWebhookInfo();
        }

        if ($this->option('remove')) {
            return $this->removeWebhook();
        }

        // Set webhook
        $url = $this->argument('url');
        if (!$url) {
            $url = $this->askForWebhookUrl();
        }

        return $this->setWebhook($url);
    }

    /**
     * Show current bot configuration
     */
    private function showBotConfig(): void
    {
        $botToken = config('services.telegram.bot_token');
        $botUsername = config('services.telegram.bot_username');
        $webhookSecret = config('services.telegram.webhook_secret');

        $this->info('Current Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Bot Token', $botToken ? (substr($botToken, 0, 20) . '...') : 'Not set'],
                ['Bot Username', $botUsername ?: 'Not set'],
                ['Webhook Secret', $webhookSecret ? 'Set (hidden)' : 'Not set'],
            ]
        );
        $this->line('');
    }

    /**
     * Test bot connection
     */
    private function testConnection(): int
    {
        $this->info('Testing bot connection...');
        
        try {
            $connected = $this->telegramService->verifyConnection();
            
            if ($connected) {
                $this->info('✅ Bot connection successful!');
                return self::SUCCESS;
            } else {
                $this->error('❌ Bot connection failed!');
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('❌ Connection test failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Show current webhook information
     */
    private function showWebhookInfo(): int
    {
        $this->info('Retrieving webhook information...');
        
        try {
            $webhookInfo = $this->telegramService->getWebhookInfo();
            
            if (empty($webhookInfo)) {
                $this->warn('No webhook information available.');
                return self::SUCCESS;
            }

            $this->info('Current Webhook Information:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['URL', $webhookInfo['url'] ?? 'Not set'],
                    ['Has Custom Certificate', isset($webhookInfo['has_custom_certificate']) ? ($webhookInfo['has_custom_certificate'] ? 'Yes' : 'No') : 'Unknown'],
                    ['Pending Updates', $webhookInfo['pending_update_count'] ?? 'Unknown'],
                    ['Last Error Date', isset($webhookInfo['last_error_date']) ? date('Y-m-d H:i:s', $webhookInfo['last_error_date']) : 'None'],
                    ['Last Error Message', $webhookInfo['last_error_message'] ?? 'None'],
                    ['Max Connections', $webhookInfo['max_connections'] ?? 'Unknown'],
                ]
            );

            if (isset($webhookInfo['allowed_updates']) && !empty($webhookInfo['allowed_updates'])) {
                $this->info('Allowed Updates: ' . implode(', ', $webhookInfo['allowed_updates']));
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to get webhook info: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Remove webhook
     */
    private function removeWebhook(): int
    {
        if (!$this->confirm('Are you sure you want to remove the webhook?')) {
            $this->info('Webhook removal cancelled.');
            return self::SUCCESS;
        }

        $this->info('Removing webhook...');
        
        try {
            $removed = $this->telegramService->removeWebhook();
            
            if ($removed) {
                $this->info('✅ Webhook removed successfully!');
                return self::SUCCESS;
            } else {
                $this->error('❌ Failed to remove webhook!');
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('❌ Webhook removal failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Ask user for webhook URL
     */
    private function askForWebhookUrl(): string
    {
        $defaultUrl = $this->generateDefaultWebhookUrl();
        
        $this->info('Default webhook URL: ' . $defaultUrl);
        $this->line('');
        
        $url = $this->ask('Enter webhook URL (or press Enter for default)', $defaultUrl);
        
        return $url;
    }

    /**
     * Generate default webhook URL based on app configuration
     */
    private function generateDefaultWebhookUrl(): string
    {
        $appUrl = config('app.url', 'https://your-domain.com');
        return rtrim($appUrl, '/') . '/api/telegram/webhook';
    }

    /**
     * Set webhook
     */
    private function setWebhook(string $url): int
    {
        // Validate URL
        $validator = Validator::make(['url' => $url], [
            'url' => 'required|url|max:255'
        ]);

        if ($validator->fails()) {
            $this->error('❌ Invalid URL format!');
            foreach ($validator->errors()->all() as $error) {
                $this->line('  ' . $error);
            }
            return self::FAILURE;
        }

        // Validate HTTPS for production
        if (app()->environment('production') && !str_starts_with($url, 'https://')) {
            $this->error('❌ HTTPS is required for production webhook URLs!');
            return self::FAILURE;
        }

        $this->info("Setting webhook to: {$url}");
        
        try {
            $set = $this->telegramService->setWebhook($url);
            
            if ($set) {
                $this->info('✅ Webhook set successfully!');
                $this->line('');
                $this->info('Next steps:');
                $this->line('1. Test the webhook by sending /start to your bot');
                $this->line('2. Check logs for webhook activity');
                $this->line('3. Use "php artisan telegram:set-webhook --info" to verify');
                return self::SUCCESS;
            } else {
                $this->error('❌ Failed to set webhook!');
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('❌ Webhook setup failed: ' . $e->getMessage());
            
            // Provide helpful error messages
            if (str_contains($e->getMessage(), 'Bad Request')) {
                $this->line('');
                $this->warn('Possible causes:');
                $this->line('- Invalid URL format');
                $this->line('- URL not accessible from Telegram servers');
                $this->line('- SSL certificate issues');
            }
            
            return self::FAILURE;
        }
    }
} 