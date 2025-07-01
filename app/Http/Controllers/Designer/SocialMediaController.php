<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\DesignerSocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SocialMediaController extends Controller
{
    /**
     * Store a new social media link
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer) {
            return redirect()->route('designer.setup');
        }

        $request->validate([
            'platform' => [
                'required',
                'string',
                Rule::in(['facebook', 'instagram', 'twitter', 'telegram', 'whatsapp', 'youtube', 'website']),
                Rule::unique('designer_social_media')->where('designer_id', $designer->id)
            ],
            'url' => 'required|url|max:255',
        ], [
            'platform.unique' => 'لديك بالفعل رابط لهذه المنصة',
            'url.url' => 'يجب أن يكون الرابط صحيحاً',
        ]);

        DesignerSocialMedia::create([
            'designer_id' => $designer->id,
            'platform' => $request->platform,
            'url' => $request->url,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'تم إضافة الرابط بنجاح');
    }

    /**
     * Update social media link
     */
    public function update(Request $request, DesignerSocialMedia $socialMedia)
    {
        $user = Auth::user();
        $designer = $user->designer;

        // Check ownership
        if (!$designer || $socialMedia->designer_id !== $designer->id) {
            abort(403);
        }

        $request->validate([
            'platform' => [
                'required',
                'string',
                Rule::in(['facebook', 'instagram', 'twitter', 'telegram', 'whatsapp', 'youtube', 'website']),
                Rule::unique('designer_social_media')->where('designer_id', $designer->id)->ignore($socialMedia->id)
            ],
            'url' => 'required|url|max:255',
            'is_active' => 'boolean',
        ], [
            'platform.unique' => 'لديك بالفعل رابط لهذه المنصة',
            'url.url' => 'يجب أن يكون الرابط صحيحاً',
        ]);

        $socialMedia->update($request->only([
            'platform',
            'url',
            'is_active',
        ]));

        return redirect()->back()->with('success', 'تم تحديث الرابط بنجاح');
    }

    /**
     * Toggle social media link status
     */
    public function toggleStatus(DesignerSocialMedia $socialMedia)
    {
        $user = Auth::user();
        $designer = $user->designer;

        // Check ownership
        if (!$designer || $socialMedia->designer_id !== $designer->id) {
            abort(403);
        }

        $socialMedia->update([
            'is_active' => !$socialMedia->is_active,
        ]);

        $status = $socialMedia->is_active ? 'تفعيل' : 'إلغاء تفعيل';
        return redirect()->back()->with('success', 'تم ' . $status . ' الرابط بنجاح');
    }

    /**
     * Delete social media link
     */
    public function destroy(DesignerSocialMedia $socialMedia)
    {
        $user = Auth::user();
        $designer = $user->designer;

        // Check ownership
        if (!$designer || $socialMedia->designer_id !== $designer->id) {
            abort(403);
        }

        $socialMedia->delete();

        return redirect()->back()->with('success', 'تم حذف الرابط بنجاح');
    }


}
