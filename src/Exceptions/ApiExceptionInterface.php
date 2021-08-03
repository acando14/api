<?php

namespace App\Exceptions;

interface ApiExceptionInterface
{
    public function getMessage();
    public function getCode();
}
