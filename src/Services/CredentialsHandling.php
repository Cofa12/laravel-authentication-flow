<?php

namespace Cofa\LaravelAuthenticationFlow\Services;

use Illuminate\Http\Request;

class CredentialsHandling
{
    public function validateCredentials(Request $request): array
    {
        foreach ($request->all() as $key => $value) {
            if($key == 'name') {
                $credentials['name'] = 'required|string';
            }else if ($key == 'password') {
                $credentials = ['password'=>'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/'];
            }else if ($key == 'email') {
                $credentials['email'] = 'required|email|exists:users,email';
            }
        }

        $request->validate($credentials);

        return $request->only(['email','password']);

    }
}