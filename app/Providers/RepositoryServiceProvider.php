<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\AppointmentRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AppointmentRepository::class, function ($app) {
            return new AppointmentRepository();
        });
    }
}