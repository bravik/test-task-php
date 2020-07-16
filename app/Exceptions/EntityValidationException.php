<?php

/**
 * @author Roman Naumenko <naumenko_subscr@mail.ru>
 */

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class EntityValidationException extends Exception
{
    /** @var array */
    private $errors;

    public function __construct(array $errors = [])
    {
        parent::__construct('Invalid data given', 400);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
