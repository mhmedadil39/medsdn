<?php

namespace Webkul\MedsdnApi\Serializer;

use GraphQL\Error\ClientAware;
use Webkul\MedsdnApi\Exception\ValidationException;

class MedsdnApiExceptionSerializer implements ClientAware
{
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function isClientSafe(): bool
    {
        return $this->exception instanceof ValidationException;
    }

    public function getCategory(): string
    {
        return 'validation';
    }

    public static function createFromException(\Throwable $exception): ClientAware
    {
        return new self($exception);
    }
}
