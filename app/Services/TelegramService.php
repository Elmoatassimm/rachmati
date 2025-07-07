<?php

namespace App\Services;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\Update;
use App\Models\Order;
use App\Models\Rachma;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class TelegramService
{
    private BotApi $telegram;
    private string $botToken;
    private int $maxRetries = 3;
    private int $retryDelay = 2; // seconds

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->telegram = new BotApi($this->botToken);
    }

    /**
     * Process webhook update from Telegram
     */
    public function processWebhookUpdate(array $updateData): bool
    {
        try {
            Log::info('Processing Telegram webhook update', ['data' => $updateData]);
            
            // Validate webhook signature if needed
            if (!$this->validateWebhookData($updateData)) {
                Log::warning('Invalid webhook data received');
                return false;
            }

            // Process message directly from update data
            if (isset($updateData['message'])) {
                return $this->processMessageData($updateData['message']);
            }
            
            // Handle other update types if needed
            if (isset($updateData['edited_message'])) {
                return $this->processMessageData($updateData['edited_message']);
            }
            
            Log::info('Webhook update processed (no message content)');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to process Telegram webhook update', [
                'error' => $e->getMessage(),
                'data' => $updateData
            ]);
            return false;
        }
    }

    /**
     * Process message data directly from webhook
     */
    private function processMessageData(array $messageData): bool
    {
        try {
            $chatId = $messageData['chat']['id'] ?? null;
            $text = $messageData['text'] ?? '';
            $from = $messageData['from'] ?? [];
            
            if (!$chatId) {
                Log::warning('No chat ID found in message data');
                return false;
            }
            
            Log::info('Processing Telegram message', [
                'chat_id' => $chatId,
                'text' => $text,
                'from' => $from
            ]);

            // Check if user is already linked - if so, don't respond
            if ($this->isUserAlreadyLinked((string)$chatId)) {
                Log::info('User already linked, ignoring message', [
                    'chat_id' => $chatId,
                    'text' => $text
                ]);
                return true; // Return true to indicate successful processing, but no response
            }

            // Handle /start command
            if (strpos($text, '/start') === 0) {
                return $this->handleStartCommand((string)$chatId, $messageData);
            }
            
            // Handle phone number for user linking
            if (preg_match('/^(\+213|0)[567]\d{8}$/', $text)) {
                return $this->handlePhoneNumber((string)$chatId, $text);
            }
            
            // Default response for unrecognized commands
            return $this->sendDefaultResponse((string)$chatId);
            
        } catch (\Exception $e) {
            Log::error('Failed to process message data', [
                'error' => $e->getMessage(),
                'message_data' => $messageData
            ]);
            return false;
        }
    }

    /**
     * Check if a user is already linked to this chat ID
     */
    private function isUserAlreadyLinked(string $chatId): bool
    {
        try {
            $user = User::where('telegram_chat_id', $chatId)->first();
            return $user !== null;
        } catch (\Exception $e) {
            Log::error('Failed to check if user is already linked', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId
            ]);
            return false; // If we can't check, allow processing to continue
        }
    }

    /**
     * Handle /start command
     */
    private function handleStartCommand(string $chatId, array $messageData): bool
    {
        try {
            $welcomeMessage = "🌟 *مرحباً بك في منصة رشمات / Bienvenue sur Rachmat Platform*\n\n";
            $welcomeMessage .= "للحصول على إشعارات الطلبات والملفات، يرجى إرسال رقم هاتفك المسجل في التطبيق\n";
            $welcomeMessage .= "Pour recevoir les notifications et fichiers, envoyez votre numéro de téléphone enregistré\n\n";
            $welcomeMessage .= "مثال / Exemple: +213555123456 أو 0555123456";

            $this->sendNotificationWithRetry($chatId, $welcomeMessage);
            
            Log::info('Start command processed', ['chat_id' => $chatId]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to handle start command', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId
            ]);
            return false;
        }
    }

    /**
     * Handle phone number for user linking
     */
    private function handlePhoneNumber(string $chatId, string $phone): bool
    {
        try {
            // Normalize phone number
            $normalizedPhone = $this->normalizePhoneNumber($phone);
            
            // Find user by phone number
            $user = User::where('phone', $normalizedPhone)->first();
            
            if (!$user) {
                $errorMessage = "❌ *لم يتم العثور على حساب بهذا الرقم / Aucun compte trouvé avec ce numéro*\n\n";
                $errorMessage .= "يرجى التأكد من رقم الهاتف أو إنشاء حساب جديد في التطبيق\n";
                $errorMessage .= "Veuillez vérifier le numéro ou créer un compte dans l'application";
                
                $this->sendNotificationWithRetry($chatId, $errorMessage);
                return false;
            }
            
            // Check if user already linked to another chat
            if ($user->telegram_chat_id && $user->telegram_chat_id !== $chatId) {
                $warningMessage = "⚠️ *هذا الحساب مرتبط برقم تليجرام آخر / Ce compte est lié à un autre Telegram*\n\n";
                $warningMessage .= "سيتم تحديث المعلومات للحساب الحالي\n";
                $warningMessage .= "Les informations seront mises à jour pour le compte actuel";
                
                $this->sendNotificationWithRetry($chatId, $warningMessage);
            }
            
            // Link user to chat ID
            $user->update(['telegram_chat_id' => $chatId]);
            
            $successMessage = "✅ *تم ربط حسابك بنجاح / Compte lié avec succès*\n\n";
            $successMessage .= "👤 الاسم / Nom: {$user->name}\n";
            $successMessage .= "📧 البريد الإلكتروني / Email: {$user->email}\n\n";
            $successMessage .= "🔔 ستتلقى الآن إشعارات الطلبات والملفات هنا\n";
            $successMessage .= "Vous recevrez maintenant les notifications et fichiers ici";
            
            $this->sendNotificationWithRetry($chatId, $successMessage);
            
            Log::info('User linked to Telegram', [
                'user_id' => $user->id,
                'chat_id' => $chatId,
                'phone' => $normalizedPhone
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to handle phone number', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'phone' => $phone
            ]);
            
            $errorMessage = "❌ *حدث خطأ في النظام / Erreur système*\n\nيرجى المحاولة مرة أخرى / Veuillez réessayer";
            $this->sendNotificationWithRetry($chatId, $errorMessage);
            
            return false;
        }
    }

    /**
     * Send default response for unrecognized commands
     */
    private function sendDefaultResponse(string $chatId): bool
    {
        $message = "🤖 *منصة رشمات / Rachmat Platform*\n\n";
        $message .= "للبدء، اكتب /start\n";
        $message .= "Pour commencer, tapez /start\n\n";
        $message .= "للمساعدة، تواصل مع الإدارة\n";
        $message .= "Pour aide, contactez l'administration";
        
        return $this->sendNotificationWithRetry($chatId, $message);
    }

    /**
     * Normalize phone number format
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Convert to +213 format
        if (substr($phone, 0, 3) === '213') {
            return '+' . $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            return '+213' . substr($phone, 1);
        } else {
            return '+213' . $phone;
        }
    }

    /**
     * Validate webhook data (basic validation)
     */
    private function validateWebhookData(array $data): bool
    {
        // Basic validation - check if update_id exists
        return isset($data['update_id']);
    }

    /**
     * Send rachma file to client via Telegram with retry mechanism
     */
    public function sendRachmaFileWithRetry(Order $order): bool
    {
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $client = $order->client;

                // Check if client has telegram chat ID
                if (!$client->telegram_chat_id) {
                    Log::warning("Client {$client->id} does not have telegram_chat_id");
                    return false;
                }

                // Handle both single-item and multi-item orders
                $allFilesToSend = [];

                if ($order->rachma_id && $order->rachma) {
                    // Single-item order (backward compatibility)
                    $filesToSend = $this->prepareFilesForDelivery($order->rachma);
                    if (!empty($filesToSend)) {
                        $allFilesToSend = array_merge($allFilesToSend, $filesToSend);
                    }
                } elseif ($order->orderItems && $order->orderItems->count() > 0) {
                    // Multi-item order
                    foreach ($order->orderItems as $item) {
                        if ($item->rachma) {
                            $filesToSend = $this->prepareFilesForDelivery($item->rachma);
                            if (!empty($filesToSend)) {
                                $allFilesToSend = array_merge($allFilesToSend, $filesToSend);
                            }
                        }
                    }
                }

                if (empty($allFilesToSend)) {
                    Log::error("No files found for order {$order->id}");
                    return false;
                }

                // If multiple files, create ZIP package
                $zipPath = null;
                if (count($allFilesToSend) > 1) {
                    $zipPath = $this->createZipPackageForOrder($order, $allFilesToSend);
                    if (!$zipPath) {
                        Log::error("Failed to create ZIP package for order {$order->id}");
                        return false;
                    }
                    $filesToSend = [$zipPath];
                } else {
                    $filesToSend = $allFilesToSend;
                }

                // Send each file
                foreach ($filesToSend as $filePath) {
                    $success = $this->sendSingleFile($client->telegram_chat_id, $filePath, $order);
                    if (!$success) {
                        // Clean up temporary ZIP if created
                        if ($zipPath && Storage::disk('private')->exists($filePath)) {
                            Storage::disk('private')->delete($filePath);
                        }
                        return false;
                    }
                }

                // Clean up temporary ZIP if created
                if ($zipPath && Storage::disk('private')->exists($zipPath)) {
                    Storage::disk('private')->delete($zipPath);
                }

                Log::info("Order files sent successfully to client {$client->id} for order {$order->id}", [
                    'attempt' => $attempt,
                    'files_count' => count($allFilesToSend),
                    'order_type' => $order->rachma_id ? 'single_item' : 'multi_item'
                ]);

                return true;

            } catch (Exception $e) {
                Log::error("Failed to send Rachma files via Telegram (attempt {$attempt}/{$this->maxRetries})", [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id,
                    'client_id' => $order->client->id
                ]);

                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay * $attempt); // Exponential backoff
                    continue;
                }

                // Final attempt failed - send notification
                $this->handleFileDeliveryFailure($order, $e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Prepare files for delivery (get file paths)
     */
    private function prepareFilesForDelivery(Rachma $rachma): array
    {
        $filePaths = [];

        // Use new multiple files system
        if ($rachma->hasFiles()) {
            foreach ($rachma->files as $file) {
                if ($file->exists()) {
                    $filePaths[] = $file->path;
                }
            }
        }
        // Fallback to single file for backward compatibility
        elseif ($rachma->file_path && Storage::disk('private')->exists($rachma->file_path)) {
            $filePaths[] = $rachma->file_path;
        }

        return $filePaths;
    }

    /**
     * Create ZIP package for multiple files
     */
    private function createZipPackage(Rachma $rachma, array $filePaths): ?string
    {
        try {
            $zipFileName = "rachma_{$rachma->id}_" . time() . ".zip";
            $zipPath = "temp/{$zipFileName}";
            $fullZipPath = Storage::disk('private')->path($zipPath);

            // Ensure temp directory exists
            Storage::disk('private')->makeDirectory('temp');

            $zip = new \ZipArchive();
            if ($zip->open($fullZipPath, \ZipArchive::CREATE) !== TRUE) {
                Log::error("Cannot create ZIP file: {$fullZipPath}");
                return null;
            }

            foreach ($filePaths as $filePath) {
                $fullFilePath = Storage::disk('private')->path($filePath);
                if (file_exists($fullFilePath)) {
                    $fileName = basename($filePath);
                    $zip->addFile($fullFilePath, $fileName);
                }
            }

            $zip->close();

            // Check if ZIP was created successfully and is within size limits
            if (file_exists($fullZipPath)) {
                $zipSize = filesize($fullZipPath);
                if ($zipSize > 50 * 1024 * 1024) { // 50MB Telegram limit
                    unlink($fullZipPath);
                    Log::error("ZIP package too large for Telegram", [
                        'rachma_id' => $rachma->id,
                        'zip_size' => $zipSize
                    ]);
                    return null;
                }

                return $zipPath;
            }

            return null;

        } catch (\Exception $e) {
            Log::error("Failed to create ZIP package", [
                'rachma_id' => $rachma->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create a ZIP package containing all files from an order (multi-item support)
     */
    private function createZipPackageForOrder(Order $order, array $filePaths): ?string
    {
        try {
            $zipFileName = "order_{$order->id}_files_" . time() . ".zip";
            $zipPath = "temp/{$zipFileName}";
            $fullZipPath = Storage::disk('private')->path($zipPath);

            // Ensure temp directory exists
            Storage::disk('private')->makeDirectory('temp');

            $zip = new \ZipArchive();
            if ($zip->open($fullZipPath, \ZipArchive::CREATE) !== TRUE) {
                Log::error("Cannot create ZIP file: {$fullZipPath}");
                return null;
            }

            // Group files by rachma to organize them in folders
            $filesByRachma = [];

            if ($order->rachma_id && $order->rachma) {
                // Single-item order
                $rachmaTitle = $this->sanitizeFileName($order->rachma->title_ar ?? $order->rachma->title_fr ?? $order->rachma->title ?? "rachma_{$order->rachma->id}");
                $filesByRachma[$rachmaTitle] = $filePaths;
            } elseif ($order->orderItems && $order->orderItems->count() > 0) {
                // Multi-item order - organize by rachma
                foreach ($order->orderItems as $item) {
                    if ($item->rachma) {
                        $rachmaTitle = $this->sanitizeFileName($item->rachma->title_ar ?? $item->rachma->title_fr ?? $item->rachma->title ?? "rachma_{$item->rachma->id}");
                        if (!isset($filesByRachma[$rachmaTitle])) {
                            $filesByRachma[$rachmaTitle] = [];
                        }

                        // Add files for this rachma
                        $rachmaFiles = $this->prepareFilesForDelivery($item->rachma);
                        $filesByRachma[$rachmaTitle] = array_merge($filesByRachma[$rachmaTitle], $rachmaFiles);
                    }
                }
            }

            // Add files to ZIP with folder structure
            foreach ($filesByRachma as $rachmaFolder => $files) {
                foreach ($files as $filePath) {
                    $fullPath = Storage::disk('private')->path($filePath);
                    if (file_exists($fullPath)) {
                        $fileName = basename($filePath);
                        // Add to folder if multiple rachmat, otherwise add to root
                        $zipEntryName = count($filesByRachma) > 1 ? "{$rachmaFolder}/{$fileName}" : $fileName;
                        $zip->addFile($fullPath, $zipEntryName);
                    } else {
                        Log::warning("File not found when creating ZIP: {$fullPath}");
                    }
                }
            }

            $zip->close();

            // Check if ZIP was created successfully and is within size limits
            if (file_exists($fullZipPath)) {
                $zipSize = filesize($fullZipPath);
                if ($zipSize > 50 * 1024 * 1024) { // 50MB Telegram limit
                    unlink($fullZipPath);
                    Log::error("Order ZIP package too large for Telegram", [
                        'order_id' => $order->id,
                        'zip_size' => $zipSize
                    ]);
                    return null;
                }

                Log::info("Order ZIP package created successfully", [
                    'order_id' => $order->id,
                    'zip_path' => $zipPath,
                    'files_count' => count($filePaths),
                    'rachmat_count' => count($filesByRachma)
                ]);

                return $zipPath;
            }

            return null;

        } catch (\Exception $e) {
            Log::error("Failed to create order ZIP package", [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Sanitize filename for use in ZIP folders
     */
    private function sanitizeFileName(string $filename): string
    {
        // Remove or replace invalid characters
        $filename = preg_replace('/[^\p{L}\p{N}\s\-_\.]/u', '', $filename);
        $filename = trim($filename);
        $filename = preg_replace('/\s+/', '_', $filename);

        return $filename ?: 'rachma_files';
    }

    /**
     * Send a single file via Telegram
     */
    private function sendSingleFile(string $chatId, string $filePath, Order $order): bool
    {
        try {
            $fullFilePath = Storage::disk('private')->path($filePath);

            if (!file_exists($fullFilePath)) {
                Log::error("File not found for sending: {$filePath}");
                return false;
            }

            $fileSize = filesize($fullFilePath);

            // Check Telegram file size limit (50MB)
            if ($fileSize > 50 * 1024 * 1024) {
                Log::error("File too large for Telegram", [
                    'file_path' => $filePath,
                    'file_size' => $fileSize
                ]);

                // Send notification about file size issue
                $message = "❌ *الملف كبير جداً / Fichier trop volumineux*\n\n";
                $message .= "يرجى تحميل الملف من التطبيق\n";
                $message .= "Veuillez télécharger le fichier depuis l'application";

                $this->sendNotificationWithRetry($chatId, $message);
                return false;
            }

            // Prepare message
            $message = $this->prepareFileMessage($order);

            // Send file
            $this->telegram->sendDocument(
                $chatId,
                new \CURLFile($fullFilePath),
                $message,
                null,
                null,
                true // disable_notification = false
            );

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send single file via Telegram", [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Handle file delivery failure
     */
    private function handleFileDeliveryFailure(Order $order, string $error): void
    {
        try {
            $client = $order->client;
            
            if ($client->telegram_chat_id) {
                $message = "⚠️ *فشل في إرسال الملف / Échec d'envoi du fichier*\n\n";
                $message .= "رقم الطلب / N° commande: `{$order->id}`\n";
                $message .= "يرجى تحميل الملف من التطبيق أو التواصل مع الإدارة\n";
                $message .= "Veuillez télécharger depuis l'app ou contacter l'administration";
                
                $this->sendNotificationWithRetry($client->telegram_chat_id, $message);
            }
            
            Log::error('File delivery failed - notification sent', [
                'order_id' => $order->id,
                'error' => $error
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send failure notification', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);
        }
    }

    /**
     * Send rachma file to client via Telegram (legacy method for backward compatibility)
     */
    public function sendRachmaFile(Order $order): bool
    {
        return $this->sendRachmaFileWithRetry($order);
    }

    /**
     * Send notification message to client with retry mechanism
     */
    public function sendNotificationWithRetry(string $chatId, string $message): bool
    {
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $this->telegram->sendMessage(
                    $chatId, 
                    $message, 
                    'Markdown', // Enable markdown formatting
                    false, // disable_web_page_preview
                    null, // reply_to_message_id
                    null  // reply_markup
                );
                
                if ($attempt > 1) {
                    Log::info("Telegram notification sent after retry", [
                        'chat_id' => $chatId,
                        'attempt' => $attempt
                    ]);
                }
                
                return true;
            } catch (Exception $e) {
                Log::error("Failed to send Telegram notification (attempt {$attempt}/{$this->maxRetries})", [
                    'error' => $e->getMessage(),
                    'chat_id' => $chatId
                ]);
                
                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay * $attempt);
                    continue;
                }
            }
        }
        
        return false;
    }

    /**
     * Send notification message to client (legacy method for backward compatibility)
     */
    public function sendNotification(string $chatId, string $message): bool
    {
        return $this->sendNotificationWithRetry($chatId, $message);
    }

    /**
     * Send file to client with improved error handling
     */
    public function sendFile(string $chatId, string $filePath, string $caption = ''): bool
    {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                Log::error("File not found for Telegram send", ['file_path' => $filePath]);
                return false;
            }
            
            // Check file size
            $fileSize = filesize($filePath);
            if ($fileSize > 50 * 1024 * 1024) {
                Log::error("File too large for Telegram", [
                    'file_path' => $filePath,
                    'file_size' => $fileSize
                ]);
                return false;
            }
            
            $this->telegram->sendDocument(
                $chatId,
                new \CURLFile($filePath),
                $caption
            );
            
            return true;
        } catch (Exception $e) {
            Log::error("Failed to send file via Telegram", [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
                'chat_id' => $chatId
            ]);
            return false;
        }
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(string $url): bool
    {
        try {
            $this->telegram->setWebhook($url);
            Log::info("Telegram webhook set successfully", ['url' => $url]);
            return true;
        } catch (Exception $e) {
            Log::error("Failed to set Telegram webhook", [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            return false;
        }
    }

    /**
     * Remove webhook
     */
    public function removeWebhook(): bool
    {
        try {
            $this->telegram->deleteWebhook();
            Log::info("Telegram webhook removed successfully");
            return true;
        } catch (Exception $e) {
            Log::error("Failed to remove Telegram webhook", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        try {
            $info = $this->telegram->getWebhookInfo();
            return json_decode(json_encode($info), true);
        } catch (Exception $e) {
            Log::error("Failed to get webhook info", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Prepare file message with order details
     */
    private function prepareFileMessage(Order $order): string
    {
        $rachma = $order->rachma;
        $designer = $rachma->designer;

        $message = "🎉 *تم تأكيد طلبك / Votre commande est confirmée*\n\n";
        $message .= "📋 *تفاصيل الطلب / Détails de la commande:*\n";
        $message .= "• رقم الطلب / N° commande: `{$order->id}`\n";
        $message .= "• اسم الرشمة / Nom Rachma: {$rachma->title}\n";
        $message .= "• المصمم / Designer: {$designer->store_name}\n";
        $message .= "• الحجم / Taille: {$rachma->size}\n";
        $message .= "• عدد الغرز / Nombre de points: {$rachma->gharazat}\n";
        $message .= "• المبلغ / Montant: {$order->amount} DA\n\n";
        $message .= "📎 *الملف المرفق / Fichier joint*\n";
        $message .= "شكراً لاختيارك منصة رشمات / Merci d'avoir choisi Rachmat Platform! 🌟";

        return $message;
    }

    /**
     * Send order confirmation notification
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        $client = $order->client;
        
        if (!$client->telegram_chat_id) {
            return false;
        }

        $message = "✅ *تم تأكيد طلبك / Votre commande est confirmée*\n\n";
        $message .= "رقم الطلب / N° commande: `{$order->id}`\n";
        $message .= "سيتم إرسال الملف قريباً / Le fichier sera envoyé bientôt 📎";

        return $this->sendNotification($client->telegram_chat_id, $message);
    }

    /**
     * Send order rejection notification
     */
    public function sendOrderRejection(Order $order, string $reason = null): bool
    {
        $client = $order->client;
        
        if (!$client->telegram_chat_id) {
            return false;
        }

        $message = "❌ *تم رفض طلبك / Votre commande a été rejetée*\n\n";
        $message .= "رقم الطلب / N° commande: `{$order->id}`\n";
        
        if ($reason) {
            $message .= "السبب / Raison: {$reason}\n";
        }
        
        $message .= "يرجى التواصل مع الإدارة / Veuillez contacter l'administration";

        return $this->sendNotification($client->telegram_chat_id, $message);
    }

    /**
     * Verify bot token and connection
     */
    public function verifyConnection(): bool
    {
        try {
            $me = $this->telegram->getMe();
            Log::info("Telegram bot connected successfully: " . $me->getUsername());
            return true;
        } catch (Exception $e) {
            Log::error("Failed to connect to Telegram bot: " . $e->getMessage());
            return false;
        }
    }
} 