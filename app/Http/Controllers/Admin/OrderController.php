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
        $query = Order::with(['client', 'rachma.designer.user', 'rachma.categories'])
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
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(15);

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
            'rachma.categories'
        ]);

        // Add URL attributes to the order and rachma
        $orderData = $order->toArray();
        $orderData['payment_proof_url'] = $order->payment_proof_url;

        if ($order->rachma) {
            $orderData['rachma']['preview_image_urls'] = $order->rachma->preview_image_urls;
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
        $validated = $request->validated();
        $oldStatus = $order->status;
        $newStatus = $validated['status'];

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
        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return redirect()
                ->route('admin.orders.index')
                ->with('success', 'تم تحديث حالة الطلب بنجاح ');
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success','تم تحديث حالة الطلب بنجاح ');
    }

    /**
     * Validate file delivery requirements before allowing order completion
     */
    private function validateFileDelivery(Order $order): array
    {
        $rachma = $order->rachma;
        $client = $order->client;

        // Check if rachma has any files
        if (!$rachma->hasFiles()) {
            return [
                'canComplete' => false,
                'message' => 'لا توجد ملفات مرتبطة بهذه الرشمة. يرجى رفع الملفات أولاً.',
                'issues' => ['no_files']
            ];
        }

        // Check if all files exist on disk
        $missingFiles = [];
        $totalSize = 0;

        foreach ($rachma->files as $file) {
            if (!$file->exists()) {
                $missingFiles[] = $file->original_name;
            } else {
                $totalSize += $file->getFileSize() ?? 0;
            }
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
            // If multiple files, we'll create a ZIP, so check if ZIP would be reasonable
            if (count($rachma->files) > 1) {
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
            'filesCount' => count($rachma->files)
        ];
    }

    /**
     * Attempt to deliver the file to the client
     */
    private function attemptFileDelivery(Order $order): bool
    {
        try {
            // Use the TelegramService to send the file
            $delivered = $this->telegramService->sendRachmaFileWithRetry($order);

            if ($delivered) {
                \Log::info("File successfully delivered for order completion", [
                    'order_id' => $order->id,
                    'client_id' => $order->client->id,
                    'rachma_id' => $order->rachma->id
                ]);
                return true;
            } else {
                \Log::warning("File delivery failed during order completion", [
                    'order_id' => $order->id,
                    'client_id' => $order->client->id,
                    'rachma_id' => $order->rachma->id
                ]);
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
        $validation = $this->validateFileDelivery($order);
        $rachma = $order->rachma;

        return response()->json([
            'canComplete' => $validation['canComplete'],
            'message' => $validation['message'],
            'issues' => $validation['issues'],
            'totalSize' => $validation['totalSize'] ?? null,
            'filesCount' => $validation['filesCount'] ?? 0,
            'clientHasTelegram' => !empty($order->client->telegram_chat_id),
            'hasFiles' => $rachma->hasFiles(),
            'files' => $rachma->files ? array_map(function($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->original_name,
                    'format' => $file->format,
                    'size' => $file->getFileSize(),
                    'exists' => $file->exists(),
                    'is_primary' => $file->is_primary
                ];
            }, $rachma->files) : [],
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
        $statusMessages = [
            'completed' => "🎉 *تم إكمال طلبك / Votre commande est terminée*\n\nالرشمة / Rachma: {$order->rachma->title}\nالمبلغ / Montant: {$order->amount} DZD\nيمكنك تحميل الملف الآن / Vous pouvez télécharger le fichier maintenant\nشكراً لثقتك بنا / Merci pour votre confiance",
            'rejected' => "❌ *تم رفض طلبك / Votre commande a été rejetée*\n\nالرشمة / Rachma: {$order->rachma->title}\nالسبب / Raison: {$order->rejection_reason}\nيرجى التواصل مع الإدارة / Veuillez contacter l'administration",
            'pending' => "🔄 *تم إعادة فتح طلبك / Votre commande a été rouverte*\n\nالرشمة / Rachma: {$order->rachma->title}\nالمبلغ / Montant: {$order->amount} DZD\nسيتم مراجعة طلبك مجدداً / Votre commande sera réexaminée",
        ];

        if (isset($statusMessages[$newStatus])) {
            $this->telegramService->sendNotification(
                $order->client->telegram_chat_id,
                $statusMessages[$newStatus]
            );
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
        $designer = $order->rachma->designer;
        $commission = $order->amount * 0.7; // 70% to designer, 30% to platform

        // Add to unpaid earnings
        $designer->increment('earnings', $commission);
    }
}
