<?php

namespace App\Http\Controllers;

use App\Models\PrivacyPolicy;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyPolicyController extends Controller
{
    /**
     * Display the active privacy policy.
     */
    public function show(): Response
    {
        $privacyPolicy = PrivacyPolicy::getActive();

        if (!$privacyPolicy) {
            abort(404, 'لم يتم العثور على سياسة الخصوصية.');
        }

        return Inertia::render('PrivacyPolicy', [
            'privacyPolicy' => $privacyPolicy,
        ]);
    }
}
