<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Passport:routes();
        View::composer(['telescope::layout'], function ($view) {
            $view->with('telescopeScriptVariables', [
                'path' => env('APP_URL') == 'https://cargo.test' ? 'telescope' : 'public/telescope',
                'timezone' => config('app.timezone'),
                'recording' => !cache('telescope:pause-recording')
            ]);
        });
    }
}
