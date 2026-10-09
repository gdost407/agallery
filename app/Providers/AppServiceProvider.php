<?php

namespace App\Providers;

use App\Http\StorageManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();
        View::composer('app.components.sidebar', function (\Illuminate\View\View $view): void {
            $view->with('storageUsage', $this->app->make(StorageManager::class)->summary(auth()->user()));
        });
    }
}
