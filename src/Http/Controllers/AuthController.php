<?php

namespace Cofa\LaravelAuthenticationFlow\Http\Controllers;

use Cofa\LaravelAuthenticationFlow\Exceptions\LoginFailedException;
use Cofa\LaravelAuthenticationFlow\Exceptions\UnauthorizedUser;
use Cofa\LaravelAuthenticationFlow\Http\Resources\AuthenticatedUser;
use Cofa\LaravelAuthenticationFlow\Services\CredentialsHandling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

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

    public function register(Request $request, AuthFactory $auth)
    {
        $this->credentialsHandling->validateCredentials($request);
        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>$request->password
        ]);

        $token = JWTAuth::attempt($request->only('email','password'));

        if (!$token)
            throw new LoginFailedException();

        $guard = config('apiauth.guard', 'api');
        $user = $auth->guard($guard)->user();

        return new AuthenticatedUser($user, $token);

    }

    public function refreshToken(Request $request, AuthFactory $auth)
    {
        $tokenHash = hash('sha256', $request->refreshToken);


        if (Cache::has('blacklisted_refresh_tokens:' . $tokenHash)) {
            throw new UnauthorizedUser('Refresh token is expired.');
        }

        try {
            $payload = JWTAuth::setToken($request->refreshToken)->getPayload();
        } catch (\Exception $e) {
            throw new UnauthorizedUser('refresh token is Expired');
        }

        if ($payload->get('token_type') !== 'refresh') {
            throw new \Exception('Invalid token type');
        }


        $user = JWTAuth::setToken($request->refreshToken)->toUser();

        $accessToken = JWTAuth::fromUser($user);

        $accessTokenTTL = config('token_ttl.ttl');

        return [
            'accessToken' => $accessToken,
            'expiresIn' => now()->addSeconds($accessTokenTTL)->toISOString(),
        ];
    }

    public function logout(Request $request,AuthFactory $auth)
    {
        try {
            $auth->guard()->logout(true);

            if ($request->has('refreshToken')) {
                $tokenHash = hash('sha256', $request->refreshToken);
                Cache::put('blacklisted_refresh_tokens:' . $tokenHash, true, now()->addMinutes(config('refresh_ttl')));
            }

        } catch (\Illuminate\Validation\UnauthorizedException $exception) {
            throw new UnauthorizedUser('Unauthorized');
        }
        return new JsonResponse(['message' => 'Logged out successfully'], 200);
    }

}