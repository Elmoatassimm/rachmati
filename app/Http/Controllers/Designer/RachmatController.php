<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Designer\StoreRachmaRequest;
use App\Http\Requests\Designer\UpdateRachmaRequest;
use App\Models\Rachma;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RachmatController extends Controller
{
    /**
     * Display a listing of designer's rachmat
     */
    public function index(Request $request)
    {
        $designer = Auth::user()->designer;

        if (!$designer) {
            return redirect()->route('designer.setup');
        }

        $query = $designer->rachmat()
            ->with(['categories'])
            ->withCount('orders')
            ->withAvg('ratings', 'rating');

        // Filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_fr', 'like', "%{$search}%")
                  ->orWhere('description_ar', 'like', "%{$search}%")
                  ->orWhere('description_fr', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        $rachmat = $query->orderBy('created_at', 'desc')->paginate(12);

        // Append query parameters to pagination links
        $rachmat->appends($request->query());

        // Add preview_image_urls to each rachma without breaking pagination structure
        $rachmat->through(function ($rachmaItem) {
            // This ensures the preview_image_urls accessor is called and cached
            $rachmaItem->append('preview_image_urls');
            return $rachmaItem;
        });

        $categories = Category::all();

        // Calculate stats
        $stats = [
            'total' => $designer->rachmat()->count(),
            'active' => $designer->rachmat()->count(), // All are active since is_active removed
            'totalSales' => $designer->rachmat()->withCount('orders')->get()->sum('orders_count'),
            'totalEarnings' => $designer->rachmat()
                ->join('orders', 'rachmat.id', '=', 'orders.rachma_id')
                ->where('orders.status', 'completed')
                ->sum('orders.amount')
        ];

        return Inertia::render('Designer/Rachmat/Index', [
            'rachmat' => $rachmat,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new rachma
     */
    public function create()
    {
        $designer = Auth::user()->designer;

        if (!$designer || !$designer->isActive()) {
            return redirect()->route('designer.dashboard')
                ->with('error', 'يجب أن يكون اشتراكك نشطاً لرفع الرشمات. يرجى تجديد اشتراكك أولاً');
        }

        $categories = Category::all();
        $partsSuggestions = \App\Models\PartsSuggestion::active()
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_fr']);

        return Inertia::render('Designer/Rachmat/Create', [
            'categories' => $categories,
            'partsSuggestions' => $partsSuggestions,
        ]);
    }

    /**
     * Store a newly created rachma
     */
    public function store(StoreRachmaRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $designer = Auth::user()->designer;

            // Store multiple files using the new RachmaFile system
            $filesData = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $index => $file) {
                    $filePath = $file->store('rachmat_files', 'private');
                    $extension = strtoupper($file->getClientOriginalExtension());

                    $filesData[] = [
                        'id' => $index + 1,
                        'path' => $filePath,
                        'original_name' => $file->getClientOriginalName(),
                        'format' => $extension,
                        'size' => $file->getSize(),
                        'is_primary' => $index === 0, // First file is primary
                        'uploaded_at' => now()->toISOString(),
                        'description' => "ملف بصيغة {$extension}"
                    ];
                }
            }

            // Store preview images
            $previewImages = [];
            if ($request->hasFile('preview_images')) {
                foreach ($request->file('preview_images') as $image) {
                    $previewImages[] = $image->store('rachmat/preview_images', 'public');
                }
            }

            $rachma = Rachma::create([
                'designer_id' => $designer->id,
                'title_ar' => $request->title_ar,
                'title_fr' => $request->title_fr,
                'description_ar' => $request->description_ar,
                'description_fr' => $request->description_fr,
                'width' => $request->width,
                'height' => $request->height,
                'gharazat' => $request->gharazat,
                'color_numbers' => $request->color_numbers,
                'price' => $request->price,
                'files' => $filesData,
                'preview_images' => $previewImages,
            ]);

            // Attach categories
            if ($request->has('categories')) {
                $rachma->categories()->attach($request->categories);
            }

            // Create parts (if provided)
            if ($request->has('parts') && is_array($request->parts)) {
                foreach ($request->parts as $index => $partData) {
                    $rachma->parts()->create([
                        'name_ar' => $partData['name_ar'],
                        'name_fr' => $partData['name_fr'] ?? null,
                        'length' => $partData['length'] ?: null,
                        'height' => $partData['height'] ?: null,
                        'stitches' => $partData['stitches'],
                        'order' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('designer.rachmat.index')
                ->with('success', 'تم رفع الرشمة بنجاح! أصبحت متاحة للعملاء الآن');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create rachma', [
                'error' => $e->getMessage(),
                'designer_id' => Auth::user()->designer->id
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الرشمة. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Display the specified rachma
     */
    public function show($id)
    {
        // Find the rachma by ID
        $rachma = Rachma::findOrFail($id);

        Log::info("rachma",["rachma"=> $rachma, "rachma_id" => $id]);
        $user = Auth::user();
        $designer = $user->designer;

        // Check if user has a designer profile
        if (!$designer) {
            abort(403, 'Designer profile not found. Please complete your designer setup.');
        }

        // Debug information for troubleshooting
        \Log::info('Authorization Debug', [
            'user_id' => $user->id,
            'designer_id' => $designer->id,
            'rachma_id' => $rachma->id,
            'rachma_designer_id' => $rachma->designer_id,
            'rachma_designer_id_type' => gettype($rachma->designer_id),
            'designer_id_type' => gettype($designer->id),
            'subscription_status' => $designer->subscription_status,
            'subscription_end_date' => $designer->subscription_end_date,
            'has_active_subscription' => $designer->hasActiveSubscription(),
        ]);

        // Check if the rachma belongs to the authenticated designer
        // Use strict comparison with type casting to ensure proper comparison
        if ((int)$rachma->designer_id !== (int)$designer->id) {
            abort(403, 'Unauthorized access to this rachma. Rachma belongs to designer ID: ' . $rachma->designer_id . ', but you are designer ID: ' . $designer->id);
        }

        $rachma->load(['categories', 'parts', 'orders' => function($query) {
            $query->with('client')->latest()->take(5);
        }]);

        // Calculate statistics
        $stats = [
            'total_orders' => $rachma->orders()->count(),
            'completed_orders' => $rachma->orders()->where('status', 'completed')->count(),
            'total_earnings' => $rachma->orders()->where('status', 'completed')->sum('amount'),
            'average_rating' => (float) ($rachma->average_rating ?? 0),
        ];

        return Inertia::render('Designer/Rachmat/Show', [
            'rachma' => $rachma,
            'stats' => $stats,
        ]);
    }

    /**
     * Remove the specified rachma
     */
    public function destroy($id)
    {
        // Find the rachma by ID
        $rachma = Rachma::findOrFail($id);

        // Check if rachma belongs to current designer
        if ($rachma->designer_id !== Auth::user()->designer->id) {
            abort(403);
        }

        // Check if rachma has orders
        if ($rachma->orders()->count() > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف الرشمة لوجود طلبات عليها');
        }

        try {
            DB::beginTransaction();

            // Delete files using the new file system
            if ($rachma->hasFiles()) {
                foreach ($rachma->files as $file) {
                    if ($file->exists()) {
                        Storage::disk('private')->delete($file->path);
                    }
                }
            }

            // Delete legacy file if exists
            if ($rachma->file_path) {
                Storage::disk('private')->delete($rachma->file_path);
            }

            // Delete preview images
            $previewImages = $rachma->preview_images ?? [];
            foreach ($previewImages as $image) {
                Storage::disk('public')->delete($image);
            }

            // Delete parts
            $rachma->parts()->delete();

            // Delete the rachma (this will also cascade delete ratings, comments, etc.)
            $rachma->delete();

            DB::commit();

            return redirect()->route('designer.rachmat.index')
                ->with('success', 'تم حذف الرشمة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete rachma', [
                'error' => $e->getMessage(),
                'rachma_id' => $rachma->id
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الرشمة. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Download the rachma file (supports both new multi-file and legacy single file)
     */
    public function downloadFile($id, $fileId = null)
    {
        // Find the rachma by ID
        $rachma = Rachma::findOrFail($id);

        // Check if rachma belongs to current designer
        if ($rachma->designer_id !== Auth::user()->designer->id) {
            abort(403);
        }

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

            return Storage::disk('private')->download($targetFile->path, $targetFile->original_name);
        }

        // Multi-file system: Create ZIP with all files
        if ($rachma->hasFiles()) {
            $files = $rachma->files;

            if (count($files) === 1) {
                // Single file - download directly
                $file = $files[0];
                if (!$file->exists()) {
                    return redirect()->back()->with('error', 'الملف غير موجود على القرص');
                }
                return Storage::disk('private')->download($file->path, $file->original_name);
            }

            // Multiple files - create ZIP
            $zip = new \ZipArchive();
            $zipFileName = 'rachma_' . $rachma->id . '_files.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Ensure temp directory exists
            if (!file_exists(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }

            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($files as $file) {
                    if ($file->exists()) {
                        $zip->addFile(Storage::disk('private')->path($file->path), $file->original_name);
                    }
                }
                $zip->close();

                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            } else {
                return redirect()->back()->with('error', 'فشل في إنشاء ملف ZIP');
            }
        }

        // Legacy single file system
        if ($rachma->file_path) {
            if (!Storage::disk('private')->exists($rachma->file_path)) {
                return redirect()->back()->with('error', 'الملف غير موجود على القرص');
            }

            $fileName = basename($rachma->file_path);
            return Storage::disk('private')->download($rachma->file_path, $fileName);
        }

        return redirect()->back()->with('error', 'لا توجد ملفات للتحميل');
    }

    /**
     * Get preview image
     */
    public function getPreviewImage($id, $imageIndex)
    {
        // Find the rachma by ID
        $rachma = Rachma::findOrFail($id);

        // Check if rachma belongs to current designer
        if ($rachma->designer_id !== Auth::user()->designer->id) {
            abort(403);
        }

        $previewImages = $rachma->preview_images ?? [];

        if (!isset($previewImages[$imageIndex])) {
            abort(404, 'صورة المعاينة غير موجودة');
        }

        $imagePath = $previewImages[$imageIndex];

        if (!Storage::disk('public')->exists($imagePath)) {
            abort(404, 'صورة المعاينة غير موجودة على القرص');
        }

        return Storage::disk('public')->response($imagePath);
    }
}