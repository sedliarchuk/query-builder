<?php

namespace Sedliarchuk\QueryBundle\Exceptions;

use Exception;

final class MissingFieldsException extends Exception
{
    protected $message = 'Oops! No fields defined here.';
}
