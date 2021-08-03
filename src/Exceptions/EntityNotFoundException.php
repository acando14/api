<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EntityNotFoundException extends \Exception implements ApiExceptionInterface
{
    public function __construct()
    {
        parent::__construct("Not Found", Response::HTTP_NOT_FOUND);
    }
}
