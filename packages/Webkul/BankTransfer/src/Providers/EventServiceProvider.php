<?php

namespace Webkul\BankTransfer\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the package.
     *
     * @var array
     */
    protected $listen = [
        \Webkul\BankTransfer\Events\PaymentProofUploaded::class => [
            \Webkul\BankTransfer\Listeners\NotifyAdminListener::class,
        ],
        \Webkul\BankTransfer\Events\PaymentApproved::class => [
            \Webkul\BankTransfer\Listeners\SendApprovalEmail::class,
        ],
        \Webkul\BankTransfer\Events\PaymentRejected::class => [
            \Webkul\BankTransfer\Listeners\SendRejectionEmail::class,
        ],
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register event listeners
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                \Illuminate\Support\Facades\Event::listen($event, $listener);
            }
        }
    }
}
