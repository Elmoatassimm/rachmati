<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(\Illuminate\Http\Request $request): void
    {

       
        // Set up polymorphic morph map for Rating relationships
        Relation::morphMap([
            'rachma' => \App\Models\Rachma::class,
            'store' => \App\Models\Designer::class,
        ]);

       
    }
}
