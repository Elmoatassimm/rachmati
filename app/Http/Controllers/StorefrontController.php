<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Rachma;
use App\Models\Designer;
use App\Models\Category;


class StorefrontController extends Controller
{
    /**
     * Display the homepage
     */
    public function index()
    {
        $featuredRachmat = Rachma::with(['designer.user', 'categories'])
            ->active()
            ->whereHas('designer', function ($q) {
                $q->where('subscription_status', 'active');
            })
            ->orderBy('average_rating', 'desc')
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(8)
            ->get();

        $categories = Category::withCount(['rachmat' => function ($query) {
                $query->active()
                      ->whereHas('designer', function ($q) {
                          $q->where('subscription_status', 'active');
                      });
            }])
            ->limit(6)
            ->get();

        $topDesigners = Designer::with('user')
            ->where('subscription_status', 'active')
            ->withCount('rachmat')
            ->orderBy('rachmat_count', 'desc')
            ->limit(6)
            ->get();

        // Get active pricing plans for public display
        $pricingPlans = \App\Models\PricingPlan::active()
            ->orderBy('duration_months')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'duration_months' => $plan->duration_months,
                    'price' => $plan->price,
                    'description' => $plan->description,
                    'formatted_price' => $plan->formatted_price,
                    'duration_text' => $plan->duration_text,
                ];
            });

        return Inertia::render('Home', [
            'featuredRachmat' => $featuredRachmat,
            'categories' => $categories,
            'topDesigners' => $topDesigners,
            'pricingPlans' => $pricingPlans,
        ]);
    }

    /**
     * Display rachmat listing with filters
     */
    public function rachmat(Request $request)
    {
        $query = Rachma::with(['designer.user', 'categories'])
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

        if ($request->has('min_gharazat') && $request->has('max_gharazat')) {
            $query->byGharazatRange($request->min_gharazat, $request->max_gharazat);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
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

        $rachmat = $query->paginate(12);

        // Add URL attributes to rachmat data
        $rachmat->getCollection()->transform(function ($item) {
            $itemData = $item->toArray();
            $itemData['preview_image_urls'] = $item->preview_image_urls;
            return $itemData;
        });

        $categories = Category::all();

        return Inertia::render('Rachmat/Index', [
            'rachmat' => $rachmat,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'category_ids', 'size', 'min_gharazat', 'max_gharazat', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display a specific rachma
     */
    public function show(Rachma $rachma)
    {
        $rachma->load([
            'designer.user',
            'designer.socialMedia',
            'categories',
            'ratings.user',
            'comments.user'
        ]);

        // Check if rachma is available
        if ($rachma->designer->subscription_status !== 'active') {
            abort(404);
        }

        // Get related rachmat from same designer
        $relatedRachmat = Rachma::where('designer_id', $rachma->designer_id)
            ->where('id', '!=', $rachma->id)
            ->active()
            ->limit(4)
            ->get();

        // Add URL attributes to rachma data
        $rachmaData = $rachma->toArray();
        $rachmaData['preview_image_urls'] = $rachma->preview_image_urls;

        // Add URL attributes to related rachmat
        $relatedRachmatData = $relatedRachmat->map(function ($item) {
            $itemData = $item->toArray();
            $itemData['preview_image_urls'] = $item->preview_image_urls;
            return $itemData;
        });

        return Inertia::render('Rachmat/Show', [
            'rachma' => $rachmaData,
            'relatedRachmat' => $relatedRachmatData,
        ]);
    }

    /**
     * Display designers listing
     */
    public function designers(Request $request)
    {
        $query = Designer::with('user')
            ->where('subscription_status', 'active')
            ->withCount('rachmat');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('store_description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['created_at', 'rachmat_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $designers = $query->paginate(12);

        return Inertia::render('Designers/Index', [
            'designers' => $designers,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display a specific designer's store
     */
    public function designerShow(Designer $designer)
    {
        $designer->load(['user', 'socialMedia']);

        // Check if designer store is active
        if ($designer->subscription_status !== 'active') {
            abort(404);
        }

        $rachmat = Rachma::where('designer_id', $designer->id)
            ->active()
            ->with(['categories'])
            ->paginate(12);

        return Inertia::render('Designers/Show', [
            'designer' => $designer,
            'rachmat' => $rachmat,
        ]);
    }
}
