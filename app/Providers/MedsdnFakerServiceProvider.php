<?php

namespace App\Providers;

use App\Console\Commands\MedsdnFake;
use Illuminate\Support\ServiceProvider;

class MedsdnFakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MedsdnFake::class,
            ]);
        }
    }
}
