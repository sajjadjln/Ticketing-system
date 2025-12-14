<?php

namespace App\Providers;

use App\Interfaces\UserRepositoryContract;
use App\Repository\UserRepositoryImp;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\AuthServiceContract;
use App\Services\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UserRepositoryContract::class, UserRepositoryImp::class);
        $this->app->singleton(AuthServiceContract::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
