<?php

namespace Webkul\BankTransfer\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Webkul\BankTransfer\Models\BankTransferPayment::class,
    ];
}
