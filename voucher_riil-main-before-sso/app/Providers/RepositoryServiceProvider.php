<?php

namespace App\Providers;

use App\Repositories\AdminRepositories;
use App\Repositories\GuestRepositories;
use App\Repositories\HeadRepositories;
use App\Repositories\Interfaces\AdminRepositoryInterfaces;
use App\Repositories\Interfaces\GuestRepositoryInterfaces;
use App\Repositories\Interfaces\HeadRepositoryInterfaces;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            AdminRepositoryInterfaces::class,
            AdminRepositories::class
        );

        $this->app->bind(
            GuestRepositoryInterfaces::class,
            GuestRepositories::class
        );

        $this->app->bind(
            HeadRepositoryInterfaces::class,
            HeadRepositories::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
