<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class TestAssetSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test assets using provided image...');

        // Check if the provided image exists
        $sourceImage = 'Pasted image.png';
        if (!file_exists($sourceImage)) {
            $this->command->error("Source image 'Pasted image.png' not found!");
            $this->command->info('Please ensure the provided image is in the root directory.');
            return;
        }

        // Create storage directories
        $this->createDirectories();

        // Copy the provided image to storage locations
        $this->copyProvidedImageToStorage($sourceImage);

        // Generate preview images for rachmat using provided image
        $this->generatePreviewImages($sourceImage);

        // Generate rachma files using provided image
        $this->generateRachmaFiles($sourceImage);

        // Generate payment proof images using provided image
        $this->generatePaymentProofImages($sourceImage);

        $this->command->info('Test assets created successfully using the provided image!');
    }

    /**
     * Create necessary storage directories
     */
    private function createDirectories(): void
    {
        // Create public directories
        $publicDirectories = [
            'images/preview',
            'payment_proofs',
            'subscription-requests/payment-proofs',
        ];

        foreach ($publicDirectories as $dir) {
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
                $this->command->info("Created public directory: {$dir}");
            }
        }

        // Create private directories
        $privateDirectories = [
            'rachmat/files',
        ];

        foreach ($privateDirectories as $dir) {
            if (!Storage::disk('private')->exists($dir)) {
                Storage::disk('private')->makeDirectory($dir);
                $this->command->info("Created private directory: {$dir}");
            }
        }
    }

    /**
     * Copy the provided image to key storage locations
     */
    private function copyProvidedImageToStorage(string $sourceImage): void
    {
        $this->command->info('Copying provided image to clean storage structure...');

        // Copy to main sample locations using correct disks
        $content = file_get_contents($sourceImage);
        if ($content !== false) {
            // Public files
            Storage::disk('public')->put('images/preview/sample_rachma.png', $content);
            Storage::disk('public')->put('payment_proofs/sample_payment.png', $content);
            $this->command->info("Copied to: public/images/preview/sample_rachma.png");
            $this->command->info("Copied to: public/payment_proofs/sample_payment.png");

            // Private files
            Storage::disk('private')->put('rachmat/files/sample_rachma.dst', $content);
            Storage::disk('private')->put('rachmat/files/sample_rachma.pes', $content);
            Storage::disk('private')->put('rachmat/files/sample_rachma.pdf', $content);
            $this->command->info("Copied to: private/rachmat/files/sample_rachma.dst");
            $this->command->info("Copied to: private/rachmat/files/sample_rachma.pes");
            $this->command->info("Copied to: private/rachmat/files/sample_rachma.pdf");
        } else {
            $this->command->error("Failed to read source image");
        }
    }

    /**
     * Generate preview images for rachmat patterns
     */
    private function generatePreviewImages(string $sourceImage): void
    {
        $this->command->info('Generating preview images...');

        $rachmatTitles = [
            'رشمة قسنطينية كلاسيكية',
            'رشمة تلمسانية فاخرة',
            'رشمة عنابية عصرية',
            'رشمة هندسية معاصرة',
            'رشمة مجردة فنية',
            'رشمة بسيطة أنيقة',
            'رشمة الحيوانات المرحة',
            'رشمة الشخصيات الكرتونية',
            'رشمة الزفاف الملكية',
            'رشمة العيد المباركة',
            'رشمة رمضان الكريم',
            'رشمة الورود الدمشقية',
            'رشمة الأشجار الخضراء',
            'رشمة البحر الأزرق',
        ];

        foreach ($rachmatTitles as $title) {
            $slug = \Illuminate\Support\Str::slug($title);

            // Generate 3 preview images for each rachma
            for ($i = 1; $i <= 3; $i++) {
                $filename = "{$slug}_{$i}.jpg";
                $this->createPlaceholderImage($filename, $title, $i, $sourceImage);
            }
        }
    }

    /**
     * Create a placeholder image file using the provided image
     */
    private function createPlaceholderImage(string $filename, string $title, int $variant, string $sourceImage): void
    {
        // Use the provided image for all preview images
        $content = file_get_contents($sourceImage);
        if ($content !== false) {
            Storage::disk('public')->put("images/preview/{$filename}", $content);
            $this->command->info("Created preview image (from provided): {$filename}");
        } else {
            // Fallback: Create a simple text-based placeholder file
            $placeholderContent = "PLACEHOLDER IMAGE\n";
            $placeholderContent .= "Filename: {$filename}\n";
            $placeholderContent .= "Title: {$title}\n";
            $placeholderContent .= "Variant: {$variant}\n";
            $placeholderContent .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
            $placeholderContent .= "This is a placeholder for preview image.\n";

            Storage::disk('public')->put("images/preview/{$filename}", $placeholderContent);
            $this->command->info("Created preview placeholder: {$filename}");
        }
    }



    /**
     * Generate rachma files using the provided image
     */
    private function generateRachmaFiles(string $sourceImage): void
    {
        $this->command->info('Generating rachma files using provided image...');

        $rachmatTitles = [
            'رشمة قسنطينية كلاسيكية',
            'رشمة تلمسانية فاخرة',
            'رشمة عنابية عصرية',
            'رشمة هندسية معاصرة',
            'رشمة مجردة فنية',
            'رشمة بسيطة أنيقة',
            'رشمة الحيوانات المرحة',
            'رشمة الشخصيات الكرتونية',
            'رشمة الزفاف الملكية',
            'رشمة العيد المباركة',
            'رشمة رمضان الكريم',
            'رشمة الورود الدمشقية',
            'رشمة الأشجار الخضراء',
            'رشمة البحر الأزرق',
        ];

        foreach ($rachmatTitles as $title) {
            $slug = \Illuminate\Support\Str::slug($title);

            // Generate multiple format files using the provided image
            $formats = ['pdf', 'dst', 'pes'];
            foreach ($formats as $format) {
                $filename = "{$slug}.{$format}";
                $this->createRachmaFileFromImage($filename, strtoupper($format), $title, $sourceImage);
            }
        }
    }

    /**
     * Create a single rachma file
     */
    private function createRachmaFile(string $filename, string $format, string $title): void
    {
        $content = $this->generateFileContent($format, $title);
        $path = 'private/rachmat/files/' . $filename;

        Storage::put($path, $content);
        $this->command->info("Created rachma file: {$filename}");
    }

    /**
     * Create a rachma file using the provided image
     */
    private function createRachmaFileFromImage(string $filename, string $format, string $title, string $sourceImage): void
    {
        // Copy the provided image as the rachma file (for testing purposes)
        $content = file_get_contents($sourceImage);
        if ($content !== false) {
            Storage::disk('private')->put("rachmat/files/{$filename}", $content);
            $this->command->info("Created rachma file (from provided image): {$filename}");
        } else {
            // Fallback to text content if copy fails
            $textContent = $this->generateFileContent($format, $title);
            Storage::disk('private')->put("rachmat/files/{$filename}", $textContent);
            $this->command->info("Created rachma file (text fallback): {$filename}");
        }
    }

    /**
     * Generate content for different file formats
     */
    private function generateFileContent(string $format, string $title): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        
        switch ($format) {
            case 'PDF':
                return "%PDF-1.4\n" .
                       "1 0 obj\n" .
                       "<<\n" .
                       "/Type /Catalog\n" .
                       "/Pages 2 0 R\n" .
                       ">>\n" .
                       "endobj\n" .
                       "% Test PDF file for: {$title}\n" .
                       "% Generated: {$timestamp}\n" .
                       "% This is a placeholder PDF file for testing purposes.\n";
                       
            case 'DST':
                return "LA:{$title}\n" .
                       "ST:1000\n" .
                       "CO:5\n" .
                       "+0+0\n" .
                       "*\n" .
                       "# DST embroidery file\n" .
                       "# Generated: {$timestamp}\n" .
                       "# This is a placeholder DST file for testing purposes.\n";
                       
            case 'PES':
                return "#PES0001\n" .
                       "CEmbOne\n" .
                       "CSewSeg\n" .
                       "# PES embroidery file for: {$title}\n" .
                       "# Generated: {$timestamp}\n" .
                       "# This is a placeholder PES file for testing purposes.\n";
                       
            default:
                return "Test file content for {$title}\nGenerated: {$timestamp}";
        }
    }

    /**
     * Generate payment proof images using the provided image
     */
    private function generatePaymentProofImages(string $sourceImage): void
    {
        $this->command->info('Generating payment proof images using provided image...');

        // Generate various payment proof samples for orders
        $orderProofTypes = [
            'ccp_receipt_1.jpg',
            'ccp_receipt_2.jpg',
            'baridi_mob_1.jpg',
            'baridi_mob_2.jpg',
            'dahabiya_receipt_1.jpg',
            'dahabiya_receipt_2.jpg',
            'bank_transfer_1.jpg',
            'bank_transfer_2.jpg',
        ];

        foreach ($orderProofTypes as $filename) {
            $this->createPaymentProofImage($filename, 'payment_proofs', $sourceImage);
        }

        // Generate payment proof samples for subscription requests
        $subscriptionProofTypes = [
            'ccp_receipt_1.jpg',
            'ccp_receipt_2.jpg',
            'baridi_mob_1.jpg',
            'baridi_mob_2.jpg',
            'dahabiya_receipt_1.jpg',
            'dahabiya_receipt_2.jpg',
        ];

        foreach ($subscriptionProofTypes as $filename) {
            $this->createPaymentProofImage($filename, 'subscription-requests/payment-proofs', $sourceImage);
        }
    }

    /**
     * Create a payment proof image using the provided image
     */
    private function createPaymentProofImage(string $filename, string $directory = 'payment_proofs', string $sourceImage = null): void
    {
        // Use the provided image for all payment proofs
        if ($sourceImage) {
            $content = file_get_contents($sourceImage);
            if ($content !== false) {
                Storage::disk('public')->put("{$directory}/{$filename}", $content);
                $this->command->info("Created payment proof (from provided image): {$directory}/{$filename}");
                return;
            }
        }

        // Create a simple text-based placeholder receipt
        $paymentType = strtoupper(explode('_', $filename)[0]);

        $placeholderContent = "PAYMENT RECEIPT - {$paymentType}\n";
        $placeholderContent .= "================================\n";
        $placeholderContent .= "Transaction ID: " . strtoupper(substr(md5($filename), 0, 8)) . "\n";
        $placeholderContent .= "Date: " . now()->format('Y-m-d H:i:s') . "\n";
        $placeholderContent .= "Amount: " . rand(2000, 25000) . " DZD\n";
        $placeholderContent .= "Status: COMPLETED\n";
        $placeholderContent .= "Reference: REF" . rand(100000, 999999) . "\n";
        $placeholderContent .= "================================\n";
        $placeholderContent .= "This is a placeholder payment proof file.\n";
        $placeholderContent .= "Generated for testing purposes.\n";

        Storage::disk('public')->put("{$directory}/{$filename}", $placeholderContent);
        $this->command->info("Created payment proof placeholder: {$directory}/{$filename}");
    }

}
