<?php

namespace Cofa\LaravelAuthenticationFlow\Http\Controllers;

use App\Services\V9\AuthenticatedUser;
use Cofa\LaravelAuthenticationFlow\Exceptions\LoginFailedException;
use Cofa\LaravelAuthenticationFlow\Http\Resources\AuthenticatedUser;
use Cofa\LaravelAuthenticationFlow\Services\CredentialsHandling;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(private CredentialsHandling $credentialsHandling)
    {
    }

    public function login(Request $request, AuthFactory $auth)
    {

        $credentials = $this->credentialsHandling->validateCredentials($request);
        $token = JWTAuth::attempt($credentials);

        if (!$token)
            throw new LoginFailedException();

        $guard = config('apiauth.guard', 'api');
        $user = $auth->guard($guard)->user();


        return new AuthenticatedUser($user, $token);
    }
}