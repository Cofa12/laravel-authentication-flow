<?php

namespace Cofa\LaravelAuthenticationFlow\Http\Resources;


use Cofa\LaravelAuthenticationFlow\Exceptions\UnauthorizedUser;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticatedUser implements Arrayable
{
    private ?string $refreshToken = null;

    public function __construct(
        private        $user,
        private string $accessToken,
    )
    {
        {
            try {

                JWTAuth::factory()->setTTL(config('apiauth.refresh_ttl'));

                $this->refreshToken = JWTAuth::claims([
                    'token_type' => 'refresh'
                ])->fromUser($this->user);

            } catch (Exception $e) {
                throw new UnauthorizedUser('refresh token is Expired');
            }
        }
    }

    public function toArray(): array
    {


        $accessTokenTTL = config('apiauth.ttl', default: 0.5);
        $refreshTokenTTL = config('apiauth.refresh_ttl');

        return [
            'profile' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                "emailVerified" => $this->user->email_verified ? true : false,
                "roles" => ['customer'],
                'documentStatus' => $this->user->documents[0]->identity_status ?? 'inActive',
                'image' => $this->user->image,
                'description' => $this->user->description,
                'phones' => $this->user->phones->map(function ($phone) {
                    return [
                        'id' => $phone->id,
                        'number' => $phone->phone,
                        'verified' => $phone->is_verified ? true : false,
                        'isPrimary' => $phone->is_primary ? true : false,
                        'createdAt' => $phone->created_at,
                    ];
                }),
                'createdAt' => $this->user->created_at,
                'updatedAt' => $this->user->updated_at,
            ],
            'credentials' => [
                'accessToken' => $this->accessToken,
                'expiresIn' => now()->addSeconds($accessTokenTTL)->toISOString(),
                'refreshToken' => $this->refreshToken,
                'refreshExpiresIn' => now()->addSeconds($refreshTokenTTL)->toISOString(),
            ]
        ];
    }
}
