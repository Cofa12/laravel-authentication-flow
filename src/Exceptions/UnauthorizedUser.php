<?php

namespace Cofa\LaravelAuthenticationFlow\Exceptions;

use Illuminate\Http\JsonResponse;

class UnauthorizedUser extends \RuntimeException
{

   public function render():JsonResponse
   {
       return new JsonResponse(['message'=>'Unauthorized'],401);
   }
}