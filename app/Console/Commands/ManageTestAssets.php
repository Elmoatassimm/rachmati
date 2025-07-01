<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ManageTestAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-assets:manage {action : Action to perform (create|clean|verify)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage test assets for the Rachmat platform';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'create':
                $this->createTestAssets();
                break;
            case 'clean':
                $this->cleanTestAssets();
                break;
            case 'verify':
                $this->verifyTestAssets();
                break;
            default:
                $this->error("Invalid action. Use: create, clean, or verify");
                return 1;
        }

        return 0;
    }

    /**
     * Create test assets
     */
    private function createTestAssets()
    {
        $this->info('Creating test assets...');
        $this->call('db:seed', ['--class' => 'TestAssetSeeder']);
        $this->info('Test assets created successfully!');
    }

    /**
     * Clean test assets
     */
    private function cleanTestAssets()
    {
        $this->info('Cleaning test assets...');

        $directories = [
            'public/images/preview',
            'public/payment_proofs',
            'public/subscription-requests/payment-proofs',
            'private/rachmat/files',
            'private/private/rachmat/files', // Handle nested structure
        ];

        $cleaned = 0;
        foreach ($directories as $dir) {
            if (Storage::exists($dir)) {
                $files = Storage::files($dir);
                foreach ($files as $file) {
                    // Only delete test files (avoid deleting real user uploads)
                    if ($this->isTestFile($file)) {
                        Storage::delete($file);
                        $cleaned++;
                    }
                }
                $this->info("Cleaned directory: {$dir}");
            }
        }

        $this->info("Cleaned {$cleaned} test files.");
    }

    /**
     * Verify test assets exist and are accessible
     */
    private function verifyTestAssets()
    {
        $this->info('Verifying test assets...');

        $checks = [
            'Preview Images' => $this->verifyPreviewImages(),
            'Rachma Files' => $this->verifyRachmaFiles(),
            'Payment Proofs (Orders)' => $this->verifyOrderPaymentProofs(),
            'Payment Proofs (Subscriptions)' => $this->verifySubscriptionPaymentProofs(),
        ];

        $this->table(['Asset Type', 'Status', 'Count'], array_map(function($type, $result) {
            return [$type, $result['status'], $result['count']];
        }, array_keys($checks), $checks));

        $allPassed = array_reduce($checks, function($carry, $check) {
            return $carry && $check['status'] === '✅ OK';
        }, true);

        if ($allPassed) {
            $this->info('All test assets verified successfully!');
        } else {
            $this->warn('Some test assets are missing or inaccessible.');
        }
    }

    /**
     * Check if a file is a test file
     */
    private function isTestFile(string $filePath): bool
    {
        $testPatterns = [
            '/^rshm-.*\.(jpg|pdf|dst|pes)$/',
            '/^ccp_receipt_\d+\.jpg$/',
            '/^baridi_mob_\d+\.jpg$/',
            '/^dahabiya_receipt_\d+\.jpg$/',
            '/^bank_transfer_\d+\.jpg$/',
        ];

        $filename = basename($filePath);
        foreach ($testPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify preview images
     */
    private function verifyPreviewImages(): array
    {
        $directory = 'images/preview';
        $files = Storage::disk('public')->files($directory);
        $testFiles = array_filter($files, [$this, 'isTestFile']);

        return [
            'status' => count($testFiles) >= 42 ? '✅ OK' : '❌ Missing',
            'count' => count($testFiles)
        ];
    }

    /**
     * Verify rachma files
     */
    private function verifyRachmaFiles(): array
    {
        $directories = [
            'private/rachmat/files',
            'private/private/rachmat/files', // Handle nested structure
        ];
        
        $totalFiles = 0;
        foreach ($directories as $directory) {
            if (Storage::exists($directory)) {
                $files = Storage::files($directory);
                $testFiles = array_filter($files, [$this, 'isTestFile']);
                $totalFiles += count($testFiles);
            }
        }
        
        return [
            'status' => $totalFiles >= 42 ? '✅ OK' : '❌ Missing',
            'count' => $totalFiles
        ];
    }

    /**
     * Verify order payment proofs
     */
    private function verifyOrderPaymentProofs(): array
    {
        $directory = 'payment_proofs';
        $files = Storage::disk('public')->files($directory);
        $testFiles = array_filter($files, [$this, 'isTestFile']);

        return [
            'status' => count($testFiles) >= 8 ? '✅ OK' : '❌ Missing',
            'count' => count($testFiles)
        ];
    }

    /**
     * Verify subscription payment proofs
     */
    private function verifySubscriptionPaymentProofs(): array
    {
        $directory = 'subscription-requests/payment-proofs';
        $files = Storage::disk('public')->exists($directory) ? Storage::disk('public')->files($directory) : [];
        $testFiles = array_filter($files, [$this, 'isTestFile']);

        return [
            'status' => count($testFiles) >= 6 ? '✅ OK' : '❌ Missing',
            'count' => count($testFiles)
        ];
    }
}
