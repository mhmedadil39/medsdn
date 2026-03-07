<?php

namespace Webkul\Wallet\Providers;

use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(ModuleServiceProvider::class);

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/menu.php', 'menu.admin');
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/acl.php', 'acl');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin-routes.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/sales-routes.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'wallet');
    }
}
