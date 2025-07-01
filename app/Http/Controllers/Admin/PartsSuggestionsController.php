<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartsSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PartsSuggestionsController extends Controller
{
    /**
     * Display a listing of parts suggestions.
     */
    public function index(Request $request)
    {
        $query = PartsSuggestion::with(['admin:id,name,email'])
            ->orderBy('created_at', 'desc');

        // Filter by active status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_fr', 'like', "%{$search}%");
            });
        }

        $suggestions = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/PartsSuggestions/Index', [
            'partsSuggestions' => $suggestions,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new parts suggestion.
     */
    public function create()
    {
        return Inertia::render('Admin/PartsSuggestions/Create');
    }

    /**
     * Store a newly created parts suggestion.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255|unique:parts_suggestions,name_ar',
            'name_fr' => 'required|string|max:255|unique:parts_suggestions,name_fr',
            'is_active' => 'boolean',
        ], [
            'name_ar.required' => 'الاسم باللغة العربية مطلوب',
            'name_ar.unique' => 'هذا الاسم باللغة العربية موجود بالفعل',
            'name_fr.required' => 'الاسم باللغة الفرنسية مطلوب',
            'name_fr.unique' => 'هذا الاسم باللغة الفرنسية موجود بالفعل',
        ]);

        PartsSuggestion::create([
            'name_ar' => $request->name_ar,
            'name_fr' => $request->name_fr,
            'admin_id' => Auth::id(),
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.parts-suggestions.index')
            ->with('success', 'تم إضافة اقتراح الجزء بنجاح');
    }

    /**
     * Display the specified parts suggestion.
     */
    public function show(PartsSuggestion $partsSuggestion)
    {
        $partsSuggestion->load(['admin:id,name,email']);

        return Inertia::render('Admin/PartsSuggestions/Show', [
            'suggestion' => $partsSuggestion,
        ]);
    }

    /**
     * Show the form for editing the specified parts suggestion.
     */
    public function edit(PartsSuggestion $partsSuggestion)
    {
        return Inertia::render('Admin/PartsSuggestions/Edit', [
            'partsSuggestion' => $partsSuggestion,
        ]);
    }

    /**
     * Update the specified parts suggestion.
     */
    public function update(Request $request, PartsSuggestion $partsSuggestion)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255|unique:parts_suggestions,name_ar,' . $partsSuggestion->id,
            'name_fr' => 'required|string|max:255|unique:parts_suggestions,name_fr,' . $partsSuggestion->id,
            'is_active' => 'boolean',
        ], [
            'name_ar.required' => 'الاسم باللغة العربية مطلوب',
            'name_ar.unique' => 'هذا الاسم باللغة العربية موجود بالفعل',
            'name_fr.required' => 'الاسم باللغة الفرنسية مطلوب',
            'name_fr.unique' => 'هذا الاسم باللغة الفرنسية موجود بالفعل',
        ]);

        $partsSuggestion->update([
            'name_ar' => $request->name_ar,
            'name_fr' => $request->name_fr,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.parts-suggestions.index')
            ->with('success', 'تم تحديث اقتراح الجزء بنجاح');
    }

    /**
     * Remove the specified parts suggestion.
     */
    public function destroy(PartsSuggestion $partsSuggestion)
    {
        $partsSuggestion->delete();

        return redirect()->route('admin.parts-suggestions.index')
            ->with('success', 'تم حذف اقتراح الجزء بنجاح');
    }

    /**
     * Toggle the active status of the parts suggestion.
     */
    public function toggleStatus(PartsSuggestion $partsSuggestion)
    {
        $partsSuggestion->update([
            'is_active' => !$partsSuggestion->is_active,
        ]);

        $status = $partsSuggestion->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
        
        return redirect()->back()
            ->with('success', "{$status} اقتراح الجزء بنجاح");
    }

    /**
     * Get active parts suggestions for API/AJAX requests.
     */
    public function getActive()
    {
        $suggestions = PartsSuggestion::active()
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_fr']);

        return response()->json($suggestions);
    }
} 