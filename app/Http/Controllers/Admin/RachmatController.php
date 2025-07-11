<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Designer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rachma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RachmatController extends Controller
{
    /**
     * Apply filters to a rachma query.
     */
    private function applyRachmatFilters(Request $request, Builder $query): Builder
    {
        if ($request->filled('designer_id') && $request->designer_id !== 'all') {
            $query->where('designer_id', $request->designer_id);
        }

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_fr', 'like', "%{$search}%")
                  ->orWhere('description_ar', 'like', "%{$search}%")
                  ->orWhere('description_fr', 'like', "%{$search}%")
                  ->orWhereHas('designer.user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    /**
     * Display a listing of all rachmat from all designers
     */
    public function index(Request $request)
    {
        // Base query for fetching rachmat data (include both order systems)
        $rachmatQuery = Rachma::query()->with([
            'designer.user', 'categories',
        ])->withCount([
            'orders', // Direct orders (legacy)
            'orderItems', // Orders through order_items table
            'ratings'
        ]);

        // Apply filters to the main query
        $rachmatQuery = $this->applyRachmatFilters($request, $rachmatQuery);

        // Paginate the results
        $rachmat = $rachmatQuery->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Base query for calculating stats on filtered data
        $statsQuery = Rachma::query();
        $statsQuery = $this->applyRachmatFilters($request, $statsQuery);

        $rachmaIds = (clone $statsQuery)->pluck('id');

        // Calculate stats based on the filtered data (include both order systems)
        $directOrders = Order::whereIn('rachma_id', $rachmaIds)->count();
        $orderItemsOrders = OrderItem::whereIn('rachma_id', $rachmaIds)->count();
        $totalOrders = $directOrders + $orderItemsOrders;

        $directRevenue = Order::whereIn('rachma_id', $rachmaIds)->where('status', 'completed')->sum('amount');
        $orderItemsRevenue = OrderItem::whereIn('rachma_id', $rachmaIds)
            ->whereHas('order', function($q) {
                $q->where('status', 'completed');
            })->sum('price');
        $totalRevenue = $directRevenue + $orderItemsRevenue;

        $stats = [
            'total_rachmat' => $rachmaIds->count(),
            'total_designers' => (clone $statsQuery)->distinct('designer_id')->count(),
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
        ];

        // Get filter options
        $designers = Designer::with('user')
                           ->whereHas('rachmat')
                           ->orderBy('store_name')
                           ->get();

        $categories = Category::whereHas('rachmat')
                            ->orderBy('name_ar')
                            ->get();

        return Inertia::render('Admin/Rachmat/Index', [
            'rachmat' => $rachmat,
            'designers' => $designers,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $request->only([
                'designer_id', 'category_id', 'date_from', 'date_to',
                'min_price', 'max_price', 'search'
            ]),
        ]);
    }

    /**
     * Display the specified rachma with detailed information
     */
    public function show(Rachma $rachma)
    {
        $rachma->load([
            'designer.user',
            'categories',
            'parts',
            'orders.client',
            'ratings.user',
            'comments.user'
        ]);

        // Load comprehensive statistics including both order systems
        $rachma->loadCount([
            'orders', // Direct orders (legacy)
            'orderItems', // Orders through order_items table
            'ratings' // Ratings count
        ]);

        // Load average rating
        $rachma->loadAvg('ratings', 'rating');

        // Calculate comprehensive statistics
        $directOrdersCount = $rachma->orders()->count();
        $orderItemsCount = $rachma->orderItems()->count();
        $totalOrders = $directOrdersCount + $orderItemsCount;

        $completedDirectOrders = $rachma->orders()->where('status', 'completed')->count();
        $completedOrderItems = $rachma->orderItems()->whereHas('order', function($q) {
            $q->where('status', 'completed');
        })->count();
        $completedOrders = $completedDirectOrders + $completedOrderItems;

        $directOrdersEarnings = $rachma->orders()->where('status', 'completed')->sum('amount');
        $orderItemsEarnings = $rachma->orderItems()->whereHas('order', function($q) {
            $q->where('status', 'completed');
        })->sum('price');
        $totalEarnings = $directOrdersEarnings + $orderItemsEarnings;

        // Set calculated values on the rachma object for frontend access
        $rachma->orders_count = $totalOrders;
        $rachma->order_items_count = $orderItemsCount;
        $rachma->orders_sum_amount = $totalEarnings;
        $rachma->average_rating = (float) ($rachma->ratings_avg_rating ?? 0);

        // Get files information (supports both new multi-file and legacy single file)
        $filesInfo = [];
        $fileInfo = null; // Keep for backward compatibility

        // Get files from new multi-file system
        $files = $rachma->files;
        if (!empty($files)) {
            foreach ($files as $file) {
                $filesInfo[] = [
                    'id' => $file->id,
                    'path' => $file->path,
                    'original_name' => $file->original_name,
                    'format' => $file->format,
                    'description' => $file->description,
                    'is_primary' => $file->is_primary,
                    'exists' => $file->exists(),
                    'size' => $file->getFileSize(),
                    'formatted_size' => $file->getFormattedSize(),
                    'uploaded_at' => $file->uploaded_at,
                    'download_url' => route('admin.rachmat.download-file-by-id', [$rachma->id, $file->id]),
                ];
            }

            // Set primary file info for backward compatibility
            $primaryFile = $rachma->getPrimaryFile();
            if ($primaryFile) {
                $fileInfo = [
                    'exists' => $primaryFile->exists(),
                    'size' => $primaryFile->getFileSize(),
                    'last_modified' => $primaryFile->uploaded_at ? $primaryFile->uploaded_at->timestamp : null,
                ];
            }
        } else {
            // Fallback to legacy single file system
            if ($rachma->file_path && Storage::disk('private')->exists($rachma->file_path)) {
                $fileInfo = [
                    'size' => Storage::disk('private')->size($rachma->file_path),
                    'last_modified' => Storage::disk('private')->lastModified($rachma->file_path),
                    'exists' => true,
                ];
            }
        }

        // Get preview images information
        $previewImagesInfo = [];
        if ($rachma->preview_images && is_array($rachma->preview_images)) {
            foreach ($rachma->preview_images as $imagePath) {
                if (!$imagePath || !is_string($imagePath)) {
                    continue; // Skip invalid paths
                }

                try {
                    if (Storage::disk('public')->exists($imagePath)) {
                        $previewImagesInfo[] = [
                            'path' => $imagePath,
                            'url' => asset("storage/{$imagePath}"),
                            'size' => Storage::disk('public')->size($imagePath),
                            'last_modified' => Storage::disk('public')->lastModified($imagePath),
                            'exists' => true,
                        ];
                    } else {
                        $previewImagesInfo[] = [
                            'path' => $imagePath,
                            'exists' => false,
                        ];
                    }
                } catch (\Exception $e) {
                    // Log the error but don't break the page
                    \Log::warning("Error processing preview image: {$imagePath}", ['error' => $e->getMessage()]);
                    $previewImagesInfo[] = [
                        'path' => $imagePath,
                        'exists' => false,
                    ];
                }
            }
        }

        return Inertia::render('Admin/Rachmat/Show', [
            'rachma' => $rachma,
            'fileInfo' => $fileInfo,
            'filesInfo' => $filesInfo,
            'previewImagesInfo' => $previewImagesInfo,
        ]);
    }

    /**
     * Download the rachma file (supports both new multi-file and legacy single file)
     */
    public function downloadFile(Rachma $rachma, $fileId = null)
    {
        // If fileId is provided, download specific file from multi-file system
        if ($fileId) {
            $files = $rachma->files;
            $targetFile = null;

            foreach ($files as $file) {
                if ($file->id == $fileId) {
                    $targetFile = $file;
                    break;
                }
            }

            if (!$targetFile) {
                return redirect()->back()->with('error', 'الملف غير موجود في قائمة الملفات');
            }

            if (!$targetFile->exists()) {
                return redirect()->back()->with('error', 'الملف غير موجود على القرص');
            }

            $fileName = $rachma->title . '_' . $targetFile->format . '.' . pathinfo($targetFile->original_name, PATHINFO_EXTENSION);
            return Storage::disk('private')->download($targetFile->path, $fileName);
        }

        // Legacy support: download primary file or single file_path
        $primaryFile = $rachma->getPrimaryFile();
        if ($primaryFile && $primaryFile->exists()) {
            $fileName = $rachma->title . '_' . $primaryFile->format . '.' . pathinfo($primaryFile->original_name, PATHINFO_EXTENSION);
            return Storage::disk('private')->download($primaryFile->path, $fileName);
        }

        // Fallback to old file_path system
        if ($rachma->file_path && Storage::disk('private')->exists($rachma->file_path)) {
            $fileName = $rachma->title . '_' . $rachma->id . '.' . pathinfo($rachma->file_path, PATHINFO_EXTENSION);
            return Storage::disk('private')->download($rachma->file_path, $fileName);
        }

        return redirect()->back()->with('error', 'لا توجد ملفات قابلة للتحميل');
    }

    /**
     * Download preview image
     */
    public function downloadPreviewImage(Rachma $rachma, $imageIndex)
    {
        if (!$rachma->preview_images || !isset($rachma->preview_images[$imageIndex])) {
            return redirect()->back()->with('error', 'الصورة غير موجودة');
        }

        $imagePath = $rachma->preview_images[$imageIndex];

        if (!Storage::disk('public')->exists($imagePath)) {
            return redirect()->back()->with('error', 'الصورة غير موجودة');
        }

        $fileName = $rachma->title . '_preview_' . ($imageIndex + 1) . '.' . pathinfo($imagePath, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($imagePath, $fileName);
    }

    /**
     * Get preview image for display
     */
    public function getPreviewImage(Rachma $rachma, $imageIndex)
    {
        if (!$rachma->preview_images || !isset($rachma->preview_images[$imageIndex])) {
            abort(404);
        }

        $imagePath = $rachma->preview_images[$imageIndex];

        if (!Storage::disk('public')->exists($imagePath)) {
            abort(404);
        }

        $file = Storage::disk('public')->get($imagePath);
        $mimeType = Storage::disk('public')->mimeType($imagePath);

        return Response::make($file, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Remove the specified rachma
     */
    public function destroy(Rachma $rachma)
    {
        try {
            // Check if rachma has orders (legacy system)
            $legacyOrdersCount = $rachma->orders()->count();

            // Check if rachma has order items (new system)
            $orderItemsCount = OrderItem::where('rachma_id', $rachma->id)->count();

            $totalOrdersCount = $legacyOrdersCount + $orderItemsCount;

            if ($totalOrdersCount > 0) {
                $errorMessage = "لا يمكن حذف الرشمة لوجود طلبات عليها:";
                if ($legacyOrdersCount > 0) {
                    $errorMessage .= " {$legacyOrdersCount} طلب مباشر";
                }
                if ($orderItemsCount > 0) {
                    if ($legacyOrdersCount > 0) $errorMessage .= " و";
                    $errorMessage .= " {$orderItemsCount} عنصر في طلبات";
                }
                $errorMessage .= ". يجب إلغاء الطلبات أولاً أو استخدام الحذف النهائي.";

                return redirect()->back()->with('error', $errorMessage);
            }

            // Delete files
            if ($rachma->file_path && Storage::disk('private')->exists($rachma->file_path)) {
                Storage::disk('private')->delete($rachma->file_path);
            }

            // Delete preview images
            if ($rachma->preview_images) {
                foreach ($rachma->preview_images as $imagePath) {
                    if (Storage::disk('public')->exists($imagePath)) {
                        Storage::disk('public')->delete($imagePath);
                    }
                }
            }

            // Delete parts
            $rachma->parts()->delete();

            // Delete ratings and comments
            $rachma->ratings()->delete();
            $rachma->comments()->delete();

            // Detach categories
            $rachma->categories()->detach();

            // Delete the rachma
            $rachma->delete();

            return redirect()->route('admin.rachmat.index')
                            ->with('success', 'تم حذف الرشمة وجميع ملفاتها بنجاح');
        } catch (\Exception $e) {
            Log::error('Error deleting rachma: ' . $e->getMessage(), [
                'rachma_id' => $rachma->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الرشمة: ' . $e->getMessage());
        }
    }

    /**
     * Force delete rachma even with orders (admin only)
     */
    public function forceDestroy(Rachma $rachma)
    {
        try {
            // Delete all orders first (legacy system)
            $legacyOrdersCount = $rachma->orders()->count();
            $rachma->orders()->delete();

            // Delete all order items (new system)
            $orderItemsCount = OrderItem::where('rachma_id', $rachma->id)->count();
            OrderItem::where('rachma_id', $rachma->id)->delete();

            $totalOrdersCount = $legacyOrdersCount + $orderItemsCount;

        // Delete files
        if ($rachma->file_path && Storage::disk('private')->exists($rachma->file_path)) {
            Storage::disk('private')->delete($rachma->file_path);
        }

        // Delete preview images
        if ($rachma->preview_images) {
            foreach ($rachma->preview_images as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }

        // Delete parts
        $rachma->parts()->delete();

        // Delete ratings and comments
        $rachma->ratings()->delete();
        $rachma->comments()->delete();

        // Detach categories
        $rachma->categories()->detach();

            // Delete the rachma
            $rachma->delete();

            $orderMessage = "تم حذف الرشمة نهائياً";
            if ($totalOrdersCount > 0) {
                $orderMessage .= " مع {$totalOrdersCount} طلب مرتبط بها";
                if ($legacyOrdersCount > 0 && $orderItemsCount > 0) {
                    $orderMessage .= " ({$legacyOrdersCount} طلب مباشر و {$orderItemsCount} عنصر طلب)";
                }
            }

            return redirect()->route('admin.rachmat.index')->with('warning', $orderMessage);
        } catch (\Exception $e) {
            Log::error('Error force deleting rachma: ' . $e->getMessage(), [
                'rachma_id' => $rachma->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الحذف النهائي: ' . $e->getMessage());
        }
    }
}
