<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use App\Models\DesignerSocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Display store management page
     */
    public function index()
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer) {
            return redirect()->route('designer.setup');
        }

        $designer->load('socialMedia');

        return Inertia::render('Designer/Store/Index', [
            'designer' => $designer,
            'socialMedia' => $designer->socialMedia()->orderBy('platform')->get(),
        ]);
    }

    /**
     * Display store preview page
     */
    public function show()
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer) {
            return redirect()->route('designer.setup');
        }

        $designer->load([
            'user',
            'socialMedia',
            'pricingPlan',
            'rachmat' => function ($query) {
                $query->withCount('orders');
            }
        ]);

        // Calculate additional stats
        $rachmatCount = $designer->rachmat()->count();
        $ordersCount = $designer->rachmat()->withCount('orders')->get()->sum('orders_count');

        return Inertia::render('Designer/Store/Show', [
            'designer' => $designer,
            'socialMedia' => $designer->socialMedia()->where('is_active', true)->orderBy('platform')->get(),
            'stats' => [
                'rachmat_count' => $rachmatCount,
                'orders_count' => $ordersCount,
            ],
        ]);
    }

    /**
     * Update store profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer) {
            return redirect()->route('designer.setup');
        }

        $request->validate([
            'store_name' => ['required', 'string', 'max:255', Rule::unique('designers')->ignore($designer->id)],
            'store_description' => 'nullable|string|max:1000',
        ]);

        $designer->update($request->only([
            'store_name',
            'store_description',
        ]));

        return redirect()->back()->with('success', 'تم تحديث معلومات المتجر بنجاح');
    }


}
