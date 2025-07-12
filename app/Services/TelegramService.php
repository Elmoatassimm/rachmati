<?php

namespace App\Services;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\Update;
use App\Models\Order;
use App\Models\Rachma;
use App\Models\User;
use App\Models\TelegramLinkingToken;
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
            $text = $messageData['text'] ?? '';

            // Check if start command has a user ID parameter
            if (preg_match('/^\/start\s+(.+)$/', $text, $matches)) {
                $parameter = trim($matches[1]);

                // Try to handle as user ID first (new method)
                if (is_numeric($parameter)) {
                    return $this->handleStartWithUserId($chatId, (int)$parameter);
                }

                // Fallback to token method for backward compatibility
                return $this->handleStartWithToken($chatId, $parameter);
            }

            // Default start command without parameter
            $welcomeMessage = "🌟 *مرحباً بك في منصة رشماتي / Bienvenue sur Rashmaati Platform*\n\n";
            $welcomeMessage .= "لربط حسابك، يرجى استخدام الرابط المرسل من التطبيق\n";
            $welcomeMessage .= "Pour lier votre compte, utilisez le lien envoyé depuis l'application\n\n";
            $welcomeMessage .= "إذا لم تحصل على الرابط، يرجى فتح التطبيق والذهاب إلى إعدادات التليجرام\n";
            $welcomeMessage .= "Si vous n'avez pas reçu le lien, ouvrez l'application et allez aux paramètres Telegram";

            $this->sendNotificationWithRetry($chatId, $welcomeMessage);

            Log::info('Start command processed (no parameter)', ['chat_id' => $chatId]);

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
     * Handle /start command with token for user linking
     */
    private function handleStartWithToken(string $chatId, string $token): bool
    {
        try {
            // Find valid token
            $linkingToken = TelegramLinkingToken::findValidToken($token);

            if (!$linkingToken) {
                $errorMessage = "❌ *رابط غير صالح أو منتهي الصلاحية / Lien invalide ou expiré*\n\n";
                $errorMessage .= "يرجى طلب رابط جديد من التطبيق\n";
                $errorMessage .= "Veuillez demander un nouveau lien depuis l'application";

                $this->sendNotificationWithRetry($chatId, $errorMessage);
                return false;
            }

            $user = $linkingToken->user;

            // Check if user already linked to another chat
            if ($user->telegram_chat_id && $user->telegram_chat_id !== $chatId) {
                $warningMessage = "⚠️ *هذا الحساب مرتبط برقم تليجرام آخر / Ce compte est lié à un autre Telegram*\n\n";
                $warningMessage .= "سيتم تحديث المعلومات للحساب الحالي\n";
                $warningMessage .= "Les informations seront mises à jour pour le compte actuel";

                $this->sendNotificationWithRetry($chatId, $warningMessage);
            }

            // Link user to chat ID
            $user->update(['telegram_chat_id' => $chatId]);

            // Delete the used token
            $linkingToken->delete();

            $successMessage = "✅ *تم ربط حسابك بنجاح / Compte lié avec succès*\n\n";
            $successMessage .= "👤 الاسم / Nom: {$user->name}\n";
            $successMessage .= "📧 البريد الإلكتروني / Email: {$user->email}\n\n";
            $successMessage .= "🔔 ستتلقى الآن إشعارات الطلبات والملفات هنا\n";
            $successMessage .= "Vous recevrez maintenant les notifications et fichiers ici";

            $this->sendNotificationWithRetry($chatId, $successMessage);

            Log::info('User linked to Telegram via token', [
                'user_id' => $user->id,
                'chat_id' => $chatId,
                'token' => $token
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to handle start with token', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'token' => $token
            ]);

            $errorMessage = "❌ *حدث خطأ في النظام / Erreur système*\n\nيرجى المحاولة مرة أخرى / Veuillez réessayer";
            $this->sendNotificationWithRetry($chatId, $errorMessage);

            return false;
        }
    }

    /**
     * Handle /start command with user ID for direct user linking
     */
    private function handleStartWithUserId(string $chatId, int $userId): bool
    {
        try {
            // Find user by ID
            $user = User::find($userId);

            if (!$user) {
                $errorMessage = "❌ *لم يتم العثور على حساب بهذا المعرف / Aucun compte trouvé avec cet identifiant*\n\n";
                $errorMessage .= "يرجى التأكد من الرابط أو إنشاء حساب جديد في التطبيق\n";
                $errorMessage .= "Veuillez vérifier le lien ou créer un compte dans l'application";

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

            Log::info('User linked to Telegram via user ID', [
                'user_id' => $user->id,
                'chat_id' => $chatId
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to handle start with user ID', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'user_id' => $userId
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
        $message = "🤖 *منصة رشماتي / Rashmaati Platform*\n\n";
        $message .= "للبدء، اكتب /start\n";
        $message .= "Pour commencer, tapez /start\n\n";
        $message .= "للمساعدة، تواصل مع الإدارة\n";
        $message .= "Pour aide, contactez l'administration";

        return $this->sendNotificationWithRetry($chatId, $message);
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

                // Send each file individually (no ZIP packaging)
                $totalFiles = count($allFilesToSend);
                $fileIndex = 1;

                foreach ($allFilesToSend as $fileInfo) {
                    $success = $this->sendSingleFileWithIndex($client->telegram_chat_id, $fileInfo, $order, $fileIndex, $totalFiles);
                    if (!$success) {
                        Log::error("Failed to send file {$fileIndex}/{$totalFiles} for order {$order->id}", [
                            'file_path' => $fileInfo['path'],
                            'original_name' => $fileInfo['original_name']
                        ]);
                        return false;
                    }
                    $fileIndex++;
                }

                // No ZIP cleanup needed since we're sending files directly

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
     * Prepare files for delivery (get file information with paths and original names)
     */
    private function prepareFilesForDelivery(Rachma $rachma): array
    {
        $fileInfos = [];

        // Use new multiple files system
        if ($rachma->hasFiles()) {
            foreach ($rachma->files as $file) {
                if ($file->exists()) {
                    $fileInfos[] = [
                        'path' => $file->path,
                        'original_name' => $file->original_name,
                        'format' => $file->format
                    ];
                }
            }
        }
        // Fallback to single file for backward compatibility
        elseif ($rachma->file_path && Storage::disk('private')->exists($rachma->file_path)) {
            $pathInfo = pathinfo($rachma->file_path);
            $fileInfos[] = [
                'path' => $rachma->file_path,
                'original_name' => $pathInfo['basename'] ?? 'rachma_file',
                'format' => strtoupper($pathInfo['extension'] ?? 'unknown')
            ];
        }

        return $fileInfos;
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
     * Send a single file via Telegram with file index information
     */
    private function sendSingleFileWithIndex(string $chatId, array $fileInfo, Order $order, int $fileIndex, int $totalFiles): bool
    {
        try {
            $filePath = $fileInfo['path'];
            $originalName = $fileInfo['original_name'];
            $format = $fileInfo['format'];

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
                    'original_name' => $originalName,
                    'file_size' => $fileSize
                ]);

                // Send notification about file size issue
                $message = "❌ *الملف كبير جداً / Fichier trop volumineux*\n\n";
                $message .= "يرجى تحميل الملف من التطبيق\n";
                $message .= "Veuillez télécharger le fichier depuis l'application";

                $this->sendNotificationWithRetry($chatId, $message);
                return false;
            }

            // Prepare message with file index
            $message = $this->prepareFileMessageWithIndex($order, $fileIndex, $totalFiles);

            // Send file with original filename to preserve extension
            $this->telegram->sendDocument(
                $chatId,
                new \CURLFile($fullFilePath, null, $originalName),
                $message,
                null,
                null,
                true // disable_notification = false
            );

            Log::info("File sent successfully via Telegram", [
                'file_path' => $filePath,
                'original_name' => $originalName,
                'format' => $format,
                'file_index' => $fileIndex,
                'total_files' => $totalFiles,
                'order_id' => $order->id
            ]);

            return true;

        } catch (Exception $e) {
            Log::error("Failed to send single file via Telegram", [
                'error' => $e->getMessage(),
                'file_path' => $fileInfo['path'] ?? 'unknown',
                'original_name' => $fileInfo['original_name'] ?? 'unknown',
                'chat_id' => $chatId,
                'order_id' => $order->id,
                'file_index' => $fileIndex,
                'total_files' => $totalFiles
            ]);
            return false;
        }
    }

    /**
     * Send a single file via Telegram (legacy method for backward compatibility)
     */
    private function sendSingleFile(string $chatId, string $filePath, Order $order): bool
    {
        // Convert legacy file path to file info format
        $pathInfo = pathinfo($filePath);
        $fileInfo = [
            'path' => $filePath,
            'original_name' => $pathInfo['basename'] ?? 'file',
            'format' => strtoupper($pathInfo['extension'] ?? 'unknown')
        ];

        return $this->sendSingleFileWithIndex($chatId, $fileInfo, $order, 1, 1);
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
     * Prepare file message with order details and file index
     */
    private function prepareFileMessageWithIndex(Order $order, int $fileIndex, int $totalFiles): string
    {
        // Header
        $message = "🎉 *تم تأكيد طلبك*\n\n";

        // Order ID
        $message .= "• *رقم الطلب:* `{$order->id}`\n\n";

        // Add file index information if multiple files
        if ($totalFiles > 1) {
            $message .= "• *الملف:* {$fileIndex}/{$totalFiles}\n\n";
        }

        // Handle both single-item and multi-item orders
        if ($order->rachma_id && $order->rachma) {
            // Single-item order (backward compatibility)
            $rachma = $order->rachma->load(['designer', 'parts']);
            $message .= $this->formatRachmaDetails($rachma, 1);
        } else {
            // Multi-item order
            $orderItems = $order->orderItems()->with(['rachma.designer', 'rachma.parts'])->get();

            foreach ($orderItems as $index => $item) {
                $rachmaNumber = $index + 1;
                $message .= $this->formatRachmaDetails($item->rachma, $rachmaNumber);

                // Add spacing between rachmat except for the last one
                if ($index < $orderItems->count() - 1) {
                    $message .= "\n";
                }
            }
        }

        $message .= "\n📎 *الملف المرفق*\n";
        $message .= "شكراً لاختيارك منصة رشماتي! 🌟";

        return $message;
    }

    /**
     * Format detailed rachma information for Telegram message
     */
    private function formatRachmaDetails(Rachma $rachma, int $rachmaNumber): string
    {
        $message = "🎨 *الرشمة {$rachmaNumber}*\n";
        $message .= "• {$rachma->title} - متجر {$rachma->designer->store_name}\n\n";

        // Get parts for this rachma
        $parts = $rachma->parts()->orderBy('order')->get();

        if ($parts->count() > 0) {
            foreach ($parts as $index => $part) {
                $partNumber = $index + 1;
                $message .= "📐 *الجزء {$partNumber}*\n";
                $message .= "• *تفاصيل الجزء:* {$part->name}\n";

                // Format dimensions and stitch count
                $dimensions = [];
                if ($part->length) {
                    $dimensions[] = "الطول: " . number_format($part->length, 1) . " سم";
                }
                if ($part->height) {
                    $dimensions[] = "العرض: " . number_format($part->height, 1) . " سم";
                }
                if ($part->stitches) {
                    $dimensions[] = "عدد الغرز: " . number_format($part->stitches);
                }

                if (!empty($dimensions)) {
                    $message .= "• " . implode(" | ", $dimensions) . "\n";
                }

                // Add spacing between parts except for the last one
                if ($index < $parts->count() - 1) {
                    $message .= "\n";
                }
            }
        } else {
            // Fallback if no parts are defined - show rachma-level dimensions if available
            $message .= "📐 *تفاصيل الرشمة*\n";

            $dimensions = [];
            if (isset($rachma->width) && $rachma->width) {
                $dimensions[] = "العرض: " . number_format($rachma->width, 1) . " سم";
            }
            if (isset($rachma->height) && $rachma->height) {
                $dimensions[] = "الطول: " . number_format($rachma->height, 1) . " سم";
            }

            if (!empty($dimensions)) {
                $message .= "• " . implode(" | ", $dimensions) . "\n";
            } else {
                $message .= "• تفاصيل الأبعاد غير متوفرة\n";
            }
        }

        return $message;
    }

    /**
     * Prepare file message with order details (legacy method for backward compatibility)
     */
    private function prepareFileMessage(Order $order): string
    {
        return $this->prepareFileMessageWithIndex($order, 1, 1);
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

        // Use the detailed Arabic format for confirmation
        $message = $this->prepareDetailedOrderConfirmation($order);

        return $this->sendNotification($client->telegram_chat_id, $message);
    }

    /**
     * Prepare detailed order confirmation message in Arabic
     */
    private function prepareDetailedOrderConfirmation(Order $order): string
    {
        // Header
        $message = "🎉 *تم تأكيد طلبك*\n\n";

        // Order ID
        $message .= "• *رقم الطلب:* `{$order->id}`\n\n";

        // Handle both single-item and multi-item orders
        if ($order->rachma_id && $order->rachma) {
            // Single-item order (backward compatibility)
            $rachma = $order->rachma->load(['designer', 'parts']);
            $message .= $this->formatRachmaDetails($rachma, 1);
        } else {
            // Multi-item order
            $orderItems = $order->orderItems()->with(['rachma.designer', 'rachma.parts'])->get();

            foreach ($orderItems as $index => $item) {
                $rachmaNumber = $index + 1;
                $message .= $this->formatRachmaDetails($item->rachma, $rachmaNumber);

                // Add spacing between rachmat except for the last one
                if ($index < $orderItems->count() - 1) {
                    $message .= "\n";
                }
            }
        }

        $message .= "\n📎 *سيتم إرسال الملفات قريباً*\n";
        $message .= "شكراً لاختيارك منصة رشماتي! 🌟";

        return $message;
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

        // Use detailed Arabic format for rejection
        $message = "❌ *تم رفض طلبك*\n\n";
        $message .= "• *رقم الطلب:* `{$order->id}`\n\n";

        // Add detailed order information
        if ($order->rachma_id && $order->rachma) {
            // Single-item order (backward compatibility)
            $rachma = $order->rachma->load(['designer', 'parts']);
            $message .= $this->formatRachmaDetails($rachma, 1);
        } else {
            // Multi-item order
            $orderItems = $order->orderItems()->with(['rachma.designer', 'rachma.parts'])->get();

            foreach ($orderItems as $index => $item) {
                $rachmaNumber = $index + 1;
                $message .= $this->formatRachmaDetails($item->rachma, $rachmaNumber);

                // Add spacing between rachmat except for the last one
                if ($index < $orderItems->count() - 1) {
                    $message .= "\n";
                }
            }
        }

        if ($reason) {
            $message .= "\n❌ *سبب الرفض:* {$reason}\n";
        }

        $message .= "\n📞 *يرجى التواصل مع الإدارة*\n";
        $message .= "نعتذر عن الإزعاج 🙏";

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