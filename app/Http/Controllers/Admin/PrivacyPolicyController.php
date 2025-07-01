<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivacyPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyPolicyController extends Controller
{
    /**
     * Display a listing of the privacy policies.
     */
    public function index(): Response
    {
        $privacyPolicies = PrivacyPolicy::latest()->paginate(10);

        return Inertia::render('Admin/PrivacyPolicy/Index', [
            'privacyPolicies' => $privacyPolicies,
        ]);
    }

    /**
     * Show the form for creating a new privacy policy.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/PrivacyPolicy/Create');
    }

    /**
     * Store a newly created privacy policy in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ], [
            'title.required' => 'عنوان سياسة الخصوصية مطلوب.',
            'title.string' => 'عنوان سياسة الخصوصية يجب أن يكون نص.',
            'title.max' => 'عنوان سياسة الخصوصية يجب ألا يتجاوز 255 حرف.',
            'content.required' => 'محتوى سياسة الخصوصية مطلوب.',
            'content.string' => 'محتوى سياسة الخصوصية يجب أن يكون نص.',
        ]);

        // If this policy is being set as active, deactivate all others
        if ($request->boolean('is_active')) {
            PrivacyPolicy::where('is_active', true)->update(['is_active' => false]);
        }

        PrivacyPolicy::create([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.privacy-policy.index')
            ->with('success', 'تم إنشاء سياسة الخصوصية بنجاح.');
    }

    /**
     * Display the specified privacy policy.
     */
    public function show(PrivacyPolicy $privacyPolicy): Response
    {
        return Inertia::render('Admin/PrivacyPolicy/Show', [
            'privacyPolicy' => $privacyPolicy,
        ]);
    }

    /**
     * Show the form for editing the specified privacy policy.
     */
    public function edit(PrivacyPolicy $privacyPolicy): Response
    {
        return Inertia::render('Admin/PrivacyPolicy/Edit', [
            'privacyPolicy' => $privacyPolicy,
        ]);
    }

    /**
     * Update the specified privacy policy in storage.
     */
    public function update(Request $request, PrivacyPolicy $privacyPolicy): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ], [
            'title.required' => 'عنوان سياسة الخصوصية مطلوب.',
            'title.string' => 'عنوان سياسة الخصوصية يجب أن يكون نص.',
            'title.max' => 'عنوان سياسة الخصوصية يجب ألا يتجاوز 255 حرف.',
            'content.required' => 'محتوى سياسة الخصوصية مطلوب.',
            'content.string' => 'محتوى سياسة الخصوصية يجب أن يكون نص.',
        ]);

        // If this policy is being set as active, deactivate all others
        if ($request->boolean('is_active') && !$privacyPolicy->is_active) {
            PrivacyPolicy::where('is_active', true)->update(['is_active' => false]);
        }

        $privacyPolicy->update([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.privacy-policy.index')
            ->with('success', 'تم تحديث سياسة الخصوصية بنجاح.');
    }

    /**
     * Remove the specified privacy policy from storage.
     */
    public function destroy(PrivacyPolicy $privacyPolicy): RedirectResponse
    {
        $privacyPolicy->delete();

        return redirect()->route('admin.privacy-policy.index')
            ->with('success', 'تم حذف سياسة الخصوصية بنجاح.');
    }

    /**
     * Toggle the active status of the specified privacy policy.
     */
    public function toggleStatus(PrivacyPolicy $privacyPolicy): RedirectResponse
    {
        if (!$privacyPolicy->is_active) {
            // Deactivate all other policies
            PrivacyPolicy::where('is_active', true)->update(['is_active' => false]);
            $privacyPolicy->update(['is_active' => true]);
            $message = 'تم تفعيل سياسة الخصوصية بنجاح.';
        } else {
            $privacyPolicy->update(['is_active' => false]);
            $message = 'تم إلغاء تفعيل سياسة الخصوصية بنجاح.';
        }

        return redirect()->route('admin.privacy-policy.index')
            ->with('success', $message);
    }
}
