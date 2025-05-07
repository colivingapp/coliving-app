<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);

        if (app()->environment('local')) {
            $source = public_path(join(DIRECTORY_SEPARATOR, ['img', 'example-coliving-space.jpg']));
            $dest   = storage_path(join(DIRECTORY_SEPARATOR, ['app', 'public', 'spaces', 'example-coliving-space.jpg']));        
        
            if (!file_exists($dest) && file_exists($source)) {
                $destDir = dirname($dest);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
        
                copy($source, $dest);
                \Log::info('Sample image copied from public/img to storage/app/public/spaces.');
            }
        }
    }
}
