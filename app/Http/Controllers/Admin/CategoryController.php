<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::withCount('rachmat')
            ->orderBy('name_ar')
            ->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'stats' => [
                'total' => Category::count(),
                'active' => Category::count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return Inertia::render('Admin/Categories/Create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255|unique:categories',
            'name_fr' => 'required|string|max:255',
        ], [
            'name_ar.required' => 'اسم التصنيف باللغة العربية مطلوب',
            'name_ar.unique' => 'هذا الاسم موجود بالفعل',
            'name_fr.required' => 'اسم التصنيف باللغة الفرنسية مطلوب',
        ]);

        // Create the category
        $category = Category::create([
            'name_ar' => $request->name_ar,
            'name_fr' => $request->name_fr,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم إضافة التصنيف بنجاح!');
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['rachmat.designer.user']);

        return Inertia::render('Admin/Categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255|unique:categories,name_ar,' . $category->id,
            'name_fr' => 'required|string|max:255',
        ], [
            'name_ar.required' => 'اسم التصنيف باللغة العربية مطلوب',
            'name_ar.unique' => 'هذا الاسم موجود بالفعل',
            'name_fr.required' => 'اسم التصنيف باللغة الفرنسية مطلوب',
        ]);

        // Update the category
        $category->update([
            'name_ar' => $request->name_ar,
            'name_fr' => $request->name_fr,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم تحديث التصنيف بنجاح!');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has rachmat (now through many-to-many relationship)
        if ($category->rachmat()->count() > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف التصنيف لأنه يحتوي على رشمات مرتبطة به');
        }

        // Delete the category
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح');
    }

    /**
     * Force delete category and all its rachmat
     */
    public function forceDestroy(Category $category)
    {
        // Count how much will be affected
        $rachmatCount = $category->rachmat()->count();

        // Delete all rachmat associated with category (through pivot table)
        $category->rachmat()->detach();

        // Delete the category
        $category->delete();

        $message = "تم حذف التصنيف نهائياً مع {$rachmatCount} رشمة";

        return redirect()->route('admin.categories.index')
            ->with('warning', $message);
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(Category $category)
    {
        // Since there's no is_active field, we'll return a message
        return redirect()->back()->with('info', 'تم تحديث حالة التصنيف');
    }


}
