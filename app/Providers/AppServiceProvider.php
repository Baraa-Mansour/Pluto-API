<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, function () { return new Client(); });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::pattern('id', '[1-9][0-9]*');
        Route::pattern('apartmentId', '[1-9][0-9]*');
        Route::pattern('governorateId', '[1-9][0-9]*');
        //Route::pattern('bookingId', '[1-9][0-9]*');
        Route::pattern('updateRequestId', '[1-9][0-9]*');
    }
}
