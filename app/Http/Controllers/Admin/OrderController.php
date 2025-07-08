<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\UpdateOrderRequest;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'client',
            'rachma.designer.user',
            'rachma.categories',
            'orderItems.rachma.designer.user',
            'orderItems.rachma.categories'
        ])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                // Search by order ID (exact or partial match)
                $q->where('id', 'like', "%{$search}%")
                // Search by client name or email
                ->orWhereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                })
                // Search by rachma title (Arabic or French)
                ->orWhereHas('rachma', function ($rachmaQuery) use ($search) {
                    $rachmaQuery->where('title_ar', 'like', "%{$search}%")
                               ->orWhere('title_fr', 'like', "%{$search}%");
                });
            });
        }

        // Date filter
        if ($request->has('date_from') && $request->date_from) {
            try {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $query->whereDate('created_at', '>=', $dateFrom);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
                Log::warning('Invalid date_from format in orders filter', [
                    'date_from' => $request->date_from,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($request->has('date_to') && $request->date_to) {
            try {
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->whereDate('created_at', '<=', $dateTo);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
                Log::warning('Invalid date_to format in orders filter', [
                    'date_to' => $request->date_to,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $orders = $query->paginate(10)->withQueryString();

        // Summary statistics (simplified system)
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'rejected' => Order::where('status', 'rejected')->count(),
            'totalRevenue' => Order::where('status', 'completed')->sum('amount'),
        ];

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'search', 'date_from', 'date_to']),
            'stats' => $stats,
        ]);
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load([
            'client',
            'rachma.designer.user',
            'rachma.categories',
            'orderItems.rachma.designer.user',
            'orderItems.rachma.categories'
        ]);

        // Add URL attributes to the order and rachma
        $orderData = $order->toArray();
        $orderData['payment_proof_url'] = $order->payment_proof_url;

        // Add preview URLs for backward compatibility (single rachma)
        if ($order->rachma) {
            $orderData['rachma']['preview_image_urls'] = $order->rachma->preview_image_urls;
        }

        // Add preview URLs for order items
        if ($order->orderItems) {
            foreach ($orderData['order_items'] as $index => $item) {
                if (isset($item['rachma'])) {
                    $rachma = $order->orderItems[$index]->rachma;
                    $orderData['order_items'][$index]['rachma']['preview_image_urls'] = $rachma->preview_image_urls;
                }
            }
        }

        return Inertia::render('Admin/Orders/Show', [
            'order' => $orderData,
        ]);
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Order $order)
    {
        $order->load([
            'client',
            'rachma.designer.user',
            'rachma.categories'
        ]);

        // Define available statuses with descriptions (simplified system)
        $statuses = [
            ['value' => 'pending', 'label' => 'معلق', 'description' => 'في انتظار المراجعة والمعالجة'],
            ['value' => 'completed', 'label' => 'مكتمل', 'description' => 'تم إكمال الطلب وتسليم الملف'],
            ['value' => 'rejected', 'label' => 'مرفوض', 'description' => 'تم رفض الطلب'],
        ];

        return Inertia::render('Admin/Orders/Edit', [
            'order' => $order,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Update the specified order status
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        // Load necessary relationships for file delivery
        $order->load([
            'client',
            'rachma',
            'orderItems.rachma'
        ]);

        $validated = $request->validated();
        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        Log::info("Order update request", [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'validated_data' => $validated
        ]);

        // Validate file delivery before allowing completion
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $deliveryValidation = $this->validateFileDelivery($order);

            if (!$deliveryValidation['canComplete']) {
                return back()->withErrors([
                    'file_delivery' => $deliveryValidation['message']
                ])->withInput();
            }
        }

        // Prepare update data - only status and admin notes
        $updateData = [
            'status' => $newStatus,
            'admin_notes' => $validated['admin_notes'],
        ];

        // Handle status-specific timestamp updates (simplified system)
        if ($oldStatus !== $newStatus) {
            switch ($newStatus) {
                case 'completed':
                    // Attempt file delivery before marking as completed
                    $fileDelivered = $this->attemptFileDelivery($order);

                    if (!$fileDelivered) {
                        return back()->withErrors([
                            'file_delivery' => 'فشل في إرسال الملف للعميل. يرجى التحقق من الاتصال والمحاولة مرة أخرى.'
                        ])->withInput();
                    }

                    // Set completion timestamps only after successful delivery
                    $updateData['completed_at'] = now();
                    $updateData['confirmed_at'] = now(); // For backward compatibility
                    $updateData['file_sent_at'] = now(); // For backward compatibility
                    break;
                case 'rejected':
                    $updateData['rejection_reason'] = $validated['rejection_reason'];
                    $updateData['rejected_at'] = now();

                    Log::info("Order {$order->id} being rejected", [
                        'rejection_reason' => $validated['rejection_reason'],
                        'admin_notes' => $validated['admin_notes']
                    ]);
                    break;
                case 'pending':
                    // Reset timestamps when returning to pending (from rejected)
                    $updateData['confirmed_at'] = null;
                    $updateData['file_sent_at'] = null;
                    $updateData['rejected_at'] = null;
                    $updateData['completed_at'] = null;
                    $updateData['rejection_reason'] = null;
                    break;
            }
        }

        // Update the order
        $order->update($updateData);

        // Send status change notifications
        if ($oldStatus !== $newStatus) {
            $this->sendStatusChangeNotification($order, $oldStatus, $newStatus);
        }

        // Handle designer earnings for completed orders (only after successful completion)
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $this->updateDesignerEarnings($order);
        }

        // Check if this is an AJAX request (for inline updates)
        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return redirect()
                ->back()
                ->with('success', 'تم تحديث حالة الطلب بنجاح ');
        }

        return redirect()
            ->back()
            ->with('success','تم تحديث حالة الطلب بنجاح ');
    }

    /**
     * Validate file delivery requirements before allowing order completion
     */
    private function validateFileDelivery(Order $order): array
    {
        $client = $order->client;

        // Handle both single-item and multi-item orders
        $rachmatToCheck = [];

        if ($order->rachma_id && $order->rachma) {
            // Single-item order (backward compatibility)
            $rachmatToCheck[] = $order->rachma;
        } elseif ($order->orderItems && $order->orderItems->count() > 0) {
            // Multi-item order
            $rachmatToCheck = $order->orderItems->map(function($item) {
                return $item->rachma;
            })->filter()->all(); // Use all() instead of toArray() to keep model instances
        }

        if (empty($rachmatToCheck)) {
            return [
                'canComplete' => false,
                'message' => 'لا توجد رشمات مرتبطة بهذا الطلب.',
                'issues' => ['no_rachmat']
            ];
        }

        // Check if all rachmat have files
        $rachmatWithoutFiles = [];
        $missingFiles = [];
        $totalSize = 0;
        $totalFilesCount = 0;

        foreach ($rachmatToCheck as $rachma) {
            if (!$rachma->hasFiles()) {
                $rachmatWithoutFiles[] = $rachma->title_ar ?? $rachma->title_fr ?? $rachma->title ?? "رشمة #{$rachma->id}";
                continue;
            }

            // Check if all files exist on disk
            foreach ($rachma->files as $file) {
                if (!$file->exists()) {
                    $missingFiles[] = $file->original_name . " (رشمة: " . ($rachma->title_ar ?? $rachma->title_fr ?? $rachma->title ?? "#{$rachma->id}") . ")";
                } else {
                    $totalSize += $file->getFileSize() ?? 0;
                    $totalFilesCount++;
                }
            }
        }

        // Check for rachmat without files
        if (!empty($rachmatWithoutFiles)) {
            return [
                'canComplete' => false,
                'message' => 'الرشمات التالية لا تحتوي على ملفات: ' . implode(', ', $rachmatWithoutFiles) . '. يرجى رفع الملفات أولاً.',
                'issues' => ['no_files'],
                'rachmatWithoutFiles' => $rachmatWithoutFiles
            ];
        }

        if (!empty($missingFiles)) {
            return [
                'canComplete' => false,
                'message' => 'بعض الملفات غير موجودة على الخادم: ' . implode(', ', $missingFiles),
                'issues' => ['files_not_found'],
                'missingFiles' => $missingFiles
            ];
        }

        // Check total file size (Telegram limit is 50MB, but for multiple files we might create ZIP)
        if ($totalSize > 50 * 1024 * 1024) {
            // If multiple files across multiple rachmat, we'll create a ZIP, so check if ZIP would be reasonable
            if ($totalFilesCount > 1) {
                // Estimate ZIP size (usually 10-30% smaller, but we'll be conservative)
                $estimatedZipSize = $totalSize * 0.8;
                if ($estimatedZipSize > 50 * 1024 * 1024) {
                    return [
                        'canComplete' => false,
                        'message' => 'حجم الملفات كبير جداً للإرسال عبر تيليجرام (' . $this->formatFileSize($totalSize) . '). يرجى ضغط الملفات أو استخدام طريقة تسليم أخرى.',
                        'issues' => ['files_too_large'],
                        'totalSize' => $totalSize
                    ];
                }
            } else {
                return [
                    'canComplete' => false,
                    'message' => 'حجم الملف كبير جداً للإرسال عبر تيليجرام (' . $this->formatFileSize($totalSize) . '). يرجى ضغط الملف أو استخدام طريقة تسليم أخرى.',
                    'issues' => ['file_too_large'],
                    'totalSize' => $totalSize
                ];
            }
        }

        // Check if client has Telegram connection
        if (!$client->telegram_chat_id) {
            return [
                'canComplete' => false,
                'message' => 'العميل لم يربط حسابه بتيليجرام. يرجى إرشاد العميل لربط حسابه أو استخدام طريقة تسليم أخرى.',
                'issues' => ['no_telegram_connection']
            ];
        }

        return [
            'canComplete' => true,
            'message' => 'جميع متطلبات التسليم متوفرة',
            'issues' => [],
            'totalSize' => $totalSize,
            'filesCount' => $totalFilesCount,
            'rachmatCount' => count($rachmatToCheck)
        ];
    }

    /**
     * Attempt to deliver the file to the client
     */
    private function attemptFileDelivery(Order $order): bool
    {
        try {
            // Use the TelegramService to send the file (handles both single and multi-item orders)
            $delivered = $this->telegramService->sendRachmaFileWithRetry($order);

            if ($delivered) {
                $logData = [
                    'order_id' => $order->id,
                    'client_id' => $order->client->id,
                ];

                // Add rachma info for logging
                if ($order->rachma_id && $order->rachma) {
                    $logData['rachma_id'] = $order->rachma->id;
                    $logData['order_type'] = 'single_item';
                } else {
                    $logData['order_items_count'] = $order->orderItems->count();
                    $logData['order_type'] = 'multi_item';
                }

                \Log::info("File successfully delivered for order completion", $logData);
                return true;
            } else {
                $logData = [
                    'order_id' => $order->id,
                    'client_id' => $order->client->id,
                ];

                if ($order->rachma_id && $order->rachma) {
                    $logData['rachma_id'] = $order->rachma->id;
                } else {
                    $logData['order_items_count'] = $order->orderItems->count();
                }

                \Log::warning("File delivery failed during order completion", $logData);
                return false;
            }
        } catch (\Exception $e) {
            \Log::error("Exception during file delivery attempt", [
                'order_id' => $order->id,
                'client_id' => $order->client->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Format file size for human reading
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Check file delivery status for order completion
     */
    public function checkFileDelivery(Order $order)
    {
        // Load necessary relationships
        $order->load([
            'client',
            'rachma',
            'orderItems.rachma'
        ]);

        $validation = $this->validateFileDelivery($order);

        // Collect all files from all rachmat in the order
        $allFiles = [];
        $hasFiles = false;

        if ($order->rachma_id && $order->rachma) {
            // Single-item order
            $hasFiles = $order->rachma->hasFiles();
            if ($hasFiles && $order->rachma->files) {
                foreach ($order->rachma->files as $file) {
                    $allFiles[] = [
                        'id' => $file->id,
                        'name' => $file->original_name,
                        'format' => $file->format,
                        'size' => $file->getFileSize(),
                        'exists' => $file->exists(),
                        'is_primary' => $file->is_primary,
                        'rachma_title' => $order->rachma->title_ar ?? $order->rachma->title_fr ?? $order->rachma->title ?? "رشمة #{$order->rachma->id}"
                    ];
                }
            }
        } elseif ($order->orderItems && $order->orderItems->count() > 0) {
            // Multi-item order
            foreach ($order->orderItems as $item) {
                if ($item->rachma && $item->rachma->hasFiles()) {
                    $hasFiles = true;
                    foreach ($item->rachma->files as $file) {
                        $allFiles[] = [
                            'id' => $file->id,
                            'name' => $file->original_name,
                            'format' => $file->format,
                            'size' => $file->getFileSize(),
                            'exists' => $file->exists(),
                            'is_primary' => $file->is_primary,
                            'rachma_title' => $item->rachma->title_ar ?? $item->rachma->title_fr ?? $item->rachma->title ?? "رشمة #{$item->rachma->id}"
                        ];
                    }
                }
            }
        }

        return response()->json([
            'canComplete' => $validation['canComplete'],
            'message' => $validation['message'],
            'issues' => $validation['issues'],
            'totalSize' => $validation['totalSize'] ?? null,
            'filesCount' => $validation['filesCount'] ?? 0,
            'rachmatCount' => $validation['rachmatCount'] ?? 1,
            'clientHasTelegram' => !empty($order->client->telegram_chat_id),
            'hasFiles' => $hasFiles,
            'files' => $allFiles,
            'recommendations' => $this->getDeliveryRecommendations($validation)
        ]);
    }

    /**
     * Get delivery recommendations based on validation issues
     */
    private function getDeliveryRecommendations(array $validation): array
    {
        $recommendations = [];

        if (in_array('no_files', $validation['issues'])) {
            $recommendations[] = 'يرجى رفع ملفات الرشمة من قبل المصمم';
        }

        if (in_array('files_not_found', $validation['issues'])) {
            $recommendations[] = 'يرجى التحقق من وجود الملفات على الخادم أو إعادة رفعها';
            if (isset($validation['missingFiles'])) {
                $recommendations[] = 'الملفات المفقودة: ' . implode(', ', $validation['missingFiles']);
            }
        }

        if (in_array('file_too_large', $validation['issues']) || in_array('files_too_large', $validation['issues'])) {
            $recommendations[] = 'يرجى ضغط الملفات أو تقسيمها إلى أجزاء أصغر';
            $recommendations[] = 'يمكن استخدام طريقة تسليم بديلة مثل البريد الإلكتروني';
            $recommendations[] = 'يمكن إرسال الملفات على دفعات منفصلة';
        }

        if (in_array('no_telegram_connection', $validation['issues'])) {
            $recommendations[] = 'يرجى إرشاد العميل لربط حسابه بتيليجرام';
            $recommendations[] = 'يمكن إرسال الملفات عبر البريد الإلكتروني كبديل';
        }

        return $recommendations;
    }

    /**
     * Download payment proof
     */
    public function downloadPaymentProof(Order $order)
    {
        if (!$order->payment_proof_path) {
            return redirect()->back()->withErrors(['file' => 'لا توجد صورة إثبات دفع']);
        }

        $filePath = storage_path('app/' . $order->payment_proof_path);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->withErrors(['file' => 'الملف غير موجود أو تم حذفه']);
        }

        return response()->download($filePath);
    }

    /**
     * Send notification for status changes (simplified system)
     */
    private function sendStatusChangeNotification(Order $order, string $oldStatus, string $newStatus): void
    {
        try {
            Log::info("Preparing status change notification", [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'rachma_id' => $order->rachma_id,
                'order_items_count' => $order->orderItems->count()
            ]);

            // Prepare order description for both single-item and multi-item orders
            $orderDescription = $this->getOrderDescription($order);

            Log::info("Order description prepared", [
                'order_id' => $order->id,
                'description' => $orderDescription
            ]);

            $statusMessages = [
                'completed' => "🎉 *تم إكمال طلبك / Votre commande est terminée*\n\n{$orderDescription}\nالمبلغ / Montant: " . number_format((float)$order->amount, 0) . " DZD\nيمكنك تحميل الملف الآن / Vous pouvez télécharger le fichier maintenant\nشكراً لثقتك بنا / Merci pour votre confiance",
                'rejected' => "❌ *تم رفض طلبك / Votre commande a été rejetée*\n\n{$orderDescription}\nالسبب / Raison: {$order->rejection_reason}\nيرجى التواصل مع الإدارة / Veuillez contacter l'administration",
                'pending' => "🔄 *تم إعادة فتح طلبك / Votre commande a été rouverte*\n\n{$orderDescription}\nالمبلغ / Montant: " . number_format((float)$order->amount, 0) . " DZD\nسيتم مراجعة طلبك مجدداً / Votre commande sera réexaminée",
            ];

            if (isset($statusMessages[$newStatus]) && $order->client->telegram_chat_id) {
                $this->telegramService->sendNotification(
                    $order->client->telegram_chat_id,
                    $statusMessages[$newStatus]
                );
                Log::info("Status change notification sent", [
                    'order_id' => $order->id,
                    'status' => $newStatus
                ]);
            } elseif (isset($statusMessages[$newStatus])) {
                Log::info("Skipping Telegram notification - client has no telegram_chat_id", [
                    'order_id' => $order->id,
                    'client_id' => $order->client->id,
                    'status' => $newStatus
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error in sendStatusChangeNotification", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get order description for notifications
     */
    private function getOrderDescription(Order $order): string
    {
        try {
            if ($order->rachma_id && $order->rachma) {
                // Single-item order
                return "الرشمة / Rachma: {$order->rachma->title}";
            } else {
                // Multi-item order
                $orderItems = $order->orderItems()->with('rachma')->get();
                $itemCount = $orderItems->count();

                if ($itemCount === 0) {
                    return "طلب / Commande: #{$order->id}";
                } elseif ($itemCount === 1) {
                    $item = $orderItems->first();
                    if ($item && $item->rachma) {
                        return "الرشمة / Rachma: {$item->rachma->title}";
                    } else {
                        return "طلب / Commande: #{$order->id}";
                    }
                } else {
                    $description = "عدد الرشمات / Nombre de Rachmas: {$itemCount}\n";
                    foreach ($orderItems as $index => $item) {
                        $itemNum = $index + 1;
                        if ($item && $item->rachma) {
                            $description .= "  {$itemNum}. {$item->rachma->title}\n";
                        } else {
                            $description .= "  {$itemNum}. [رشمة غير متوفرة]\n";
                        }
                    }
                    return trim($description);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error in getOrderDescription", [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return "طلب / Commande: #{$order->id}";
        }
    }

    /**
     * Send rachma file via Telegram when order is completed
     */
    private function sendRachmaFile(Order $order): void
    {
        try {
            $sent = $this->telegramService->sendRachmaFileWithRetry($order);
            
            if ($sent) {
                Log::info("Rachma file sent via Telegram for completed order", [
                    'order_id' => $order->id,
                    'client_id' => $order->client->id
                ]);
            } else {
                Log::warning("Failed to send rachma file via Telegram", [
                    'order_id' => $order->id,
                    'client_id' => $order->client->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception while sending rachma file via Telegram", [
                'order_id' => $order->id,
                'client_id' => $order->client->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update designer earnings when order is completed
     */
    private function updateDesignerEarnings(Order $order): void
    {
        // Handle single-item orders (legacy)
        if ($order->rachma_id && $order->rachma) {
            $designer = $order->rachma->designer;

            // Add full amount to unpaid earnings (100% to designer)
            $designer->increment('earnings', $order->amount);
            return;
        }

        // Handle multi-item orders
        if ($order->orderItems && $order->orderItems->count() > 0) {
            // Group order items by designer to calculate earnings per designer
            $designerEarnings = [];

            foreach ($order->orderItems as $orderItem) {
                if ($orderItem->rachma && $orderItem->rachma->designer) {
                    $designerId = $orderItem->rachma->designer->id;

                    if (!isset($designerEarnings[$designerId])) {
                        $designerEarnings[$designerId] = [
                            'designer' => $orderItem->rachma->designer,
                            'earnings' => 0
                        ];
                    }

                    // Add full item price to designer earnings (100% to designer)
                    $designerEarnings[$designerId]['earnings'] += $orderItem->price;
                }
            }

            // Update earnings for each designer
            foreach ($designerEarnings as $designerData) {
                $designerData['designer']->increment('earnings', $designerData['earnings']);
            }
        }
    }
}
