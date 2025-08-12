<?php

namespace Cofa\LaravelAuthenticationFlow\Services;

use Illuminate\Http\Request;

class CredentialsHandling
{
    public function validateCredentials(Request $request): array
    {
        $credentials = ['password'=>'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/'];
        $criticalField = 'email';

        if($request->has('phone')){
            $credentials = ['phone'=>'required|phone:AUTO|exists:users,phone'];
            $criticalField = 'phone';
        }
        else if($request->has('email')){
            $credentials = ['email'=>'required|email,exists:users,email'];
        }


        $request->validate($credentials);

        return $request->only([$criticalField,'password']);

    }
}