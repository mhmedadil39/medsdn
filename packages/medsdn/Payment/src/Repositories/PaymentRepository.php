<?php

namespace Webkul\Payment\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Payment\Contracts\Payment as PaymentContract;

class PaymentRepository extends Repository
{
    public function model(): string
    {
        return PaymentContract::class;
    }
}
