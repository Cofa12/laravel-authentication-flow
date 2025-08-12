<?php

namespace Cofa\LaravelAuthenticationFlow\Exceptions;

use Illuminate\Http\JsonResponse;

class LoginFailedException extends \RuntimeException
{
    public function render():JsonResponse
    {
        return new JsonResponse(['message'=>'Login failed'],401);
    }
}