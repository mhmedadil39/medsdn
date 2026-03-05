<?php

namespace Webkul\MedsdnApi\Input;

/**
 * LoginInput DTO for customer login credentials
 */
class LoginInput
{
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
    ) {}
}
