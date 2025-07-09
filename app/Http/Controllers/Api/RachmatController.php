<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rachma;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class RachmatController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Display a listing of rachmat with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Set locale if provided
            if ($request->has('lang') && in_array($request->lang, ['ar', 'fr'])) {
                app()->setLocale($request->lang);
            }

            $query = Rachma::with(['designer', 'categories', 'parts'])
                ->active()
                ->whereHas('designer', function ($q) {
                    $q->where('subscription_status', 'active');
                });

            // Apply filters
            if ($request->has('category_id')) {
                $query->byCategory($request->category_id);
            }

            if ($request->has('category_ids')) {
                $categoryIds = is_array($request->category_ids) ? $request->category_ids : explode(',', $request->category_ids);
                $query->byCategories($categoryIds);
            }

            if ($request->has('size')) {
                $query->bySize($request->size);
            }

            // Filter by dimensions (width/height)
            if ($request->has('width')) {
                $query->where('width', $request->width);
            }

            if ($request->has('height')) {
                $query->where('height', $request->height);
            }

            if ($request->has('min_gharazat') && $request->has('max_gharazat')) {
                $query->byGharazatRange($request->min_gharazat, $request->max_gharazat);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('title_ar', 'like', "%{$search}%")
                      ->orWhere('title_fr', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('description_ar', 'like', "%{$search}%")
                      ->orWhere('description_fr', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSorts = ['created_at', 'price', 'average_rating'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } elseif ($sortBy === 'sales_count') {
                // Replace sales_count with orders count
                $query->withCount('orders')->orderBy('orders_count', $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 50);
            $rachmat = $query->paginate($perPage);

            // Add URL attributes and localized data to rachmat
            $rachmat->getCollection()->transform(function ($item) {
                $itemData = $item->toArray();
                $itemData['preview_image_urls'] = $item->preview_image_urls;
                $itemData['localized_title'] = $item->localized_title;
                $itemData['localized_description'] = $item->localized_description;
                $itemData['formatted_size'] = $item->formatted_size;

                // Add localized category names
                if (isset($itemData['categories'])) {
                    $itemData['categories'] = collect($itemData['categories'])->map(function ($category) {
                        $category['localized_name'] = $category['name_ar'] ?? $category['name'];
                        if (app()->getLocale() === 'fr') {
                            $category['localized_name'] = $category['name_fr'] ?? $category['name_ar'] ?? $category['name'];
                        }
                        return $category;
                    })->toArray();
                }

                // Add localized part names
                if (isset($itemData['parts'])) {
                    $itemData['parts'] = collect($itemData['parts'])->map(function ($part) {
                        $part['localized_name'] = $part['name_ar'] ?? $part['name'];
                        if (app()->getLocale() === 'fr') {
                            $part['localized_name'] = $part['name_fr'] ?? $part['name_ar'] ?? $part['name'];
                        }
                        return $part;
                    })->toArray();
                }

                return $itemData;
            });

            return response()->json([
                'success' => true,
                'data' => $rachmat,
                'locale' => app()->getLocale(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rachmat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified rachma
     */
    public function show(string $id, Request $request): JsonResponse
    {
        try {
            // Set locale if provided
            if ($request->has('lang') && in_array($request->lang, ['ar', 'fr'])) {
                app()->setLocale($request->lang);
            }

            $rachma = Rachma::with([
                'designer',
                'parts',
                'categories',
                'ratings.user',
                'comments.user'
            ])
            ->active()
            ->whereHas('designer', function ($q) {
                $q->where('subscription_status', 'active');
            })
            ->findOrFail($id);

            // Get related rachmat from same designer
            $relatedRachmat = Rachma::where('designer_id', $rachma->designer_id)
                ->where('id', '!=', $rachma->id)
                ->active()
                ->limit(4)
                ->get();

            // Add URL attributes and localized data to rachma
            $rachmaData = $rachma->toArray();
            $rachmaData['preview_image_urls'] = $rachma->preview_image_urls;
            $rachmaData['localized_title'] = $rachma->localized_title;
            $rachmaData['localized_description'] = $rachma->localized_description;
            $rachmaData['formatted_size'] = $rachma->formatted_size;

            // Add localized category names
            if (isset($rachmaData['categories'])) {
                $rachmaData['categories'] = collect($rachmaData['categories'])->map(function ($category) {
                    $category['localized_name'] = $category['name_ar'] ?? $category['name'];
                    if (app()->getLocale() === 'fr') {
                        $category['localized_name'] = $category['name_fr'] ?? $category['name_ar'] ?? $category['name'];
                    }
                    return $category;
                })->toArray();
            }

            // Add localized part names
            if (isset($rachmaData['parts'])) {
                $rachmaData['parts'] = collect($rachmaData['parts'])->map(function ($part) {
                    $part['localized_name'] = $part['name_ar'] ?? $part['name'];
                    if (app()->getLocale() === 'fr') {
                        $part['localized_name'] = $part['name_fr'] ?? $part['name_ar'] ?? $part['name'];
                    }
                    return $part;
                })->toArray();
            }

            // Add URL attributes and localized data to related rachmat
            $relatedRachmatData = $relatedRachmat->map(function ($item) {
                $itemData = $item->toArray();
                $itemData['preview_image_urls'] = $item->preview_image_urls;
                $itemData['localized_title'] = $item->localized_title;
                $itemData['localized_description'] = $item->localized_description;
                $itemData['formatted_size'] = $item->formatted_size;
                return $itemData;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'rachma' => $rachmaData,
                    'related_rachmat' => $relatedRachmatData,
                ],
                'locale' => app()->getLocale(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rachma not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get categories
     */
    public function categories(Request $request): JsonResponse
    {
        try {
            // Set locale if provided
            if ($request->has('lang') && in_array($request->lang, ['ar', 'fr'])) {
                app()->setLocale($request->lang);
            }

            $categories = Category::withCount('rachmat')
                ->get();

            // Add localized names
            $categories->transform(function ($category) {
                $categoryData = $category->toArray();
                $categoryData['localized_name'] = $category->localized_name;
                return $categoryData;
            });

            return response()->json([
                'success' => true,
                'data' => $categories,
                'locale' => app()->getLocale(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular rachmat
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            // Set locale if provided
            if ($request->has('lang') && in_array($request->lang, ['ar', 'fr'])) {
                app()->setLocale($request->lang);
            }

            $rachmat = Rachma::with(['designer.user', 'categories', 'parts'])
                ->active()
                ->whereHas('designer', function ($q) {
                    $q->where('subscription_status', 'active');
                })
                ->withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->orderBy('average_rating', 'desc')
                ->limit(10)
                ->get();

            // Add URL attributes and localized data to rachmat
            $rachmatData = $rachmat->map(function ($item) {
                $itemData = $item->toArray();
                $itemData['preview_image_urls'] = $item->preview_image_urls;
                $itemData['localized_title'] = $item->localized_title;
                $itemData['localized_description'] = $item->localized_description;
                $itemData['formatted_size'] = $item->formatted_size;

                // Add localized category names
                if (isset($itemData['categories'])) {
                    $itemData['categories'] = collect($itemData['categories'])->map(function ($category) {
                        $category['localized_name'] = $category['name_ar'] ?? $category['name'];
                        if (app()->getLocale() === 'fr') {
                            $category['localized_name'] = $category['name_fr'] ?? $category['name_ar'] ?? $category['name'];
                        }
                        return $category;
                    })->toArray();
                }

                return $itemData;
            });

            return response()->json([
                'success' => true,
                'data' => $rachmatData,
                'locale' => app()->getLocale(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular rachmat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get designer details with rachmat, ratings and comments
     */
    public function designer(string $id): JsonResponse
    {
        try {
            $designer = \App\Models\Designer::with([
                'user',
                'socialMedia' => function ($query) {
                    $query->active();
                },
                'rachmat' => function ($query) {
                    $query->active()
                        ->with(['categories', 'ratings.user', 'comments.user'])
                        ->withCount('orders')
                        ->orderBy('created_at', 'desc');
                },
                'ratings.user',
                'comments.user'
            ])
            ->where('subscription_status', 'active')
            ->findOrFail($id);

            // Check if subscription is still valid
            if (!$designer->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'المصمم غير متاح حالياً'
                ], 404);
            }

            // Add URL attributes to rachmat data
            $designerData = $designer->toArray();
            $designerData['rachmat'] = $designer->rachmat->map(function ($item) {
                $itemData = $item->toArray();
                $itemData['preview_image_urls'] = $item->preview_image_urls;
                return $itemData;
            });

            // Add computed attributes
            $designerData['average_rating'] = $designer->average_rating;
            $designerData['total_sales'] = $designer->total_sales;
            $designerData['unpaid_earnings'] = $designer->unpaid_earnings;
            $designerData['rachmat_count'] = $designer->rachmat->count();

            return response()->json([
                'success' => true,
                'data' => $designerData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'المصمم غير موجود',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get active parts suggestions
     */
    public function partsSuggestions(Request $request): JsonResponse
    {
        try {
            // Set locale if provided
            if ($request->has('lang') && in_array($request->lang, ['ar', 'fr'])) {
                app()->setLocale($request->lang);
            }

            $suggestions = \App\Models\PartsSuggestion::active()
                ->orderBy('name_ar')
                ->get(['id', 'name_ar', 'name_fr']);

            // Add localized name based on current locale
            $suggestions->transform(function ($suggestion) {
                $suggestionData = $suggestion->toArray();
                $suggestionData['localized_name'] = $suggestion->localized_name;
                $suggestionData['display_name'] = $suggestion->localized_name; // For backward compatibility
                return $suggestionData;
            });

            return response()->json([
                'success' => true,
                'data' => $suggestions,
                'locale' => app()->getLocale(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch parts suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download rachma files for client
     */
    public function downloadFiles(Request $request, string $rachmaId): JsonResponse
    {
        try {
            // Get client from authenticated user
            $client = $request->user();
            
            // Ensure user is a client
            if ($client->user_type !== 'client') {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول إلى هذه الخدمة'
                ], 403);
            }

            $rachma = Rachma::with(['designer'])->findOrFail($rachmaId);

            // Check if client has purchased this rachma (support both legacy and new order systems)
            $order = Order::where('client_id', $client->id)
                ->where('status', 'completed')
                ->where(function ($query) use ($rachma) {
                    // Legacy single-item orders
                    $query->where('rachma_id', $rachma->id)
                        // OR new multi-item orders
                        ->orWhereHas('orderItems', function ($subQuery) use ($rachma) {
                            $subQuery->where('rachma_id', $rachma->id);
                        });
                })
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب شراء الرشمة أولاً للتمكن من تحميل الملفات'
                ], 403);
            }

            // Check if files exist
            $files = $rachma->getDownloadableFiles();
            if (empty($files)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد ملفات متاحة للتحميل'
                ], 404);
            }

            // Create a temporary zip file
            $zipName = 'rachma_' . $rachma->id . '_files_' . time() . '.zip';
            $zipPath = storage_path('app/temp/' . $zipName);

            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                foreach ($files as $file) {
                    $filePath = storage_path('app/' . $file['path']);
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, $file['name']);
                    }
                }
                $zip->close();

                // Generate download URL
                $downloadUrl = url('api/download-temp/' . basename($zipPath));

                // Log download activity
                Log::info('Client downloaded rachma files', [
                    'client_id' => $client->id,
                    'rachma_id' => $rachma->id,
                    'order_id' => $order->id,
                    'zip_file' => $zipName
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء ملف التحميل بنجاح',
                    'data' => [
                        'download_url' => $downloadUrl,
                        'expires_in' => 3600, // 1 hour
                        'file_count' => count($files),
                        'rachma_title' => $rachma->localized_title
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في إنشاء ملف التحميل'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to create download for rachma files', [
                'error' => $e->getMessage(),
                'rachma_id' => $rachmaId,
                'client_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء ملف التحميل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend rachma files via Telegram
     */
    public function resendTelegramFiles(Request $request, string $rachmaId): JsonResponse
    {
        try {
            // Get client from authenticated user
            $client = $request->user();
            
            // Ensure user is a client
            if ($client->user_type !== 'client') {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول إلى هذه الخدمة'
                ], 403);
            }

            $rachma = Rachma::with(['designer'])->findOrFail($rachmaId);

            // Check if client has Telegram linked
            if (!$client->telegram_chat_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم ربط حساب التليجرام بعد'
                ], 400);
            }

            // Check if client has purchased this rachma (support both legacy and new order systems)
            $order = Order::where('client_id', $client->id)
                ->where(function ($query) use ($rachma) {
                    // Legacy single-item orders
                    $query->where('rachma_id', $rachma->id)
                        // OR new multi-item orders
                        ->orWhereHas('orderItems', function ($subQuery) use ($rachma) {
                            $subQuery->where('rachma_id', $rachma->id);
                        });
                })
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب شراء الرشمة أولاً للتمكن من إرسال الملفات'
                ], 403);
            }

            if ($order->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'طلبك قيد المعالجة. يرجى الانتظار حتى يتم إكمال الطلب'
                ], 403);
            }

            // Get downloadable files
            $files = $rachma->getDownloadableFiles();
            if (empty($files)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد ملفات متاحة للإرسال'
                ], 404);
            }

            // Send files via Telegram
            $message = "🎨 إعادة إرسال ملفات الرشمة\n\n";
            $message .= "📋 العنوان: {$rachma->localized_title}\n";
            $message .= "🏪 المصمم: {$rachma->designer->store_name}\n";
            $message .= "📦 رقم الطلب: #{$order->id}\n\n";
            $message .= "📎 الملفات المرفقة أدناه:";

            // Send initial message
            $this->telegramService->sendNotification($client->telegram_chat_id, $message);

            $sentFiles = 0;
            foreach ($files as $file) {
                $filePath = storage_path('app/' . $file['path']);
                if (file_exists($filePath)) {
                    $success = $this->telegramService->sendFile(
                        $client->telegram_chat_id,
                        $filePath,
                        $file['name']
                    );
                    if ($success) {
                        $sentFiles++;
                    }
                }
            }

            // Send completion message
            $completionMessage = $sentFiles > 0 
                ? "✅ تم إرسال {$sentFiles} ملف بنجاح"
                : "❌ فشل في إرسال الملفات";
            
            $this->telegramService->sendNotification($client->telegram_chat_id, $completionMessage);

            // Log resend activity
            Log::info('Rachma files resent via Telegram', [
                'client_id' => $client->id,
                'rachma_id' => $rachma->id,
                'order_id' => $order->id,
                'files_sent' => $sentFiles,
                'total_files' => count($files)
            ]);

            return response()->json([
                'success' => true,
                'message' => $sentFiles > 0 
                    ? "تم إرسال {$sentFiles} ملف عبر التليجرام بنجاح"
                    : 'فشل في إرسال الملفات عبر التليجرام',
                'data' => [
                    'files_sent' => $sentFiles,
                    'total_files' => count($files),
                    'rachma_title' => $rachma->localized_title
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to resend rachma files via Telegram', [
                'error' => $e->getMessage(),
                'rachma_id' => $rachmaId,
                'client_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الملفات عبر التليجرام',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlink client account from Telegram
     */
    public function unlinkTelegram(Request $request): JsonResponse
    {
        try {
            // Get client from authenticated user
            $client = $request->user();
            
            // Ensure user is a client
            if ($client->user_type !== 'client') {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول إلى هذه الخدمة'
                ], 403);
            }

            if (!$client->telegram_chat_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'الحساب غير مرتبط بالتليجرام'
                ], 400);
            }

            $oldChatId = $client->telegram_chat_id;

            // Update client to remove telegram link
            $client->update([
                'telegram_chat_id' => null,
            ]);

            // Send goodbye message to Telegram
            try {
                $message = "👋 تم إلغاء ربط حسابك بالتليجرام\n\n";
                $message .= "📱 يمكنك إعادة ربط حسابك في أي وقت من خلال التطبيق\n";
                $message .= "🔗 شكراً لاستخدام خدماتنا!";
                
                $this->telegramService->sendNotification($oldChatId, $message);
            } catch (\Exception $e) {
                // Ignore telegram send errors during unlink
                Log::warning('Failed to send unlink message to Telegram', [
                    'chat_id' => $oldChatId,
                    'error' => $e->getMessage()
                ]);
            }

            // Log unlink activity
            Log::info('Client unlinked from Telegram', [
                'client_id' => $client->id,
                'old_chat_id' => $oldChatId,
                'client_name' => $client->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء ربط حسابك بالتليجرام بنجاح',
                'data' => [
                    'client_id' => $client->id,
                    'unlinked_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to unlink client from Telegram', [
                'error' => $e->getMessage(),
                'client_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء ربط الحساب',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}



