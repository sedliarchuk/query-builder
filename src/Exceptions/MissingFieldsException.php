<?php

namespace Sedliarchuk\QueryBuilder\Exceptions;

use Exception;

final class MissingFieldsException extends Exception
{
    protected $message = 'Oops! No fields defined here.';
}
