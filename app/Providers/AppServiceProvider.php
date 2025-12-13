<?php

namespace App\Providers;

use App\Interfaces\IUserRepository;
use App\Repository\UserRepositoryImp;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\IAuthService;
use App\Services\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IUserRepository::class, UserRepositoryImp::class);
        $this->app->bind(IAuthService::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
