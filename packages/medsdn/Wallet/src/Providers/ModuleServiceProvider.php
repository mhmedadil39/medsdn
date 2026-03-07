<?php

namespace Webkul\Wallet\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array<int, class-string>
     */
    protected $models = [
        \Webkul\Wallet\Models\Wallet::class,
        \Webkul\Wallet\Models\WalletTransaction::class,
        \Webkul\Wallet\Models\WalletHold::class,
    ];
}
