<?php

namespace Webkul\MedsdnApi\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    public function boot(): void
    {
        parent::boot();

    }

    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Webkul\MedsdnApi\Models\GuestCartTokens::class,
    ];
}
