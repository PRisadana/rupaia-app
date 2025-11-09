<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Content;
use App\Models\Folder;
use App\Policies\ContentPolicy;
use App\Policies\FolderPolicy;

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
    public function boot(): void
    {
        Gate::policy(Content::class, ContentPolicy::class);
        Gate::policy(Folder::class, FolderPolicy::class);
    }
}
