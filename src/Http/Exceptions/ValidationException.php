<?php

namespace Engelsystem\Http\Exceptions;

use Engelsystem\Http\Validation\Validator;
use RuntimeException;
use Throwable;

class ValidationException extends RuntimeException
{
    protected Validator $validator;

    /**
     * @param Throwable|null $previous
     */
    public function __construct(
        Validator $validator,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->validator = $validator;
        parent::__construct($message, $code, $previous);
    }

    public function getValidator(): Validator
    {
        return $this->validator;
    }
}
