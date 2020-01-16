<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use JWTAuth;
use Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $rules = [
            'email' => 'required',
            'password' => 'required',
        ];
        $validator = Validator::make($credentials, $rules);

        if( $validator->fails() )
        {
            return response()->json([
                'success' => false,
                'msg' => $validator->messages()
            ], 500);
        }

        if( !$token = JWTAuth::attempt($credentials) )
        {
            return response()->json([
                'success' => false,
                'error' => 'invalid.credentials',
                'msg' => 'Invalid Credentials.'
            ], 500);
        }

        $user = Auth::user();

        if( !$user->isAdmin() )
        {
            return response()->json([
                'success' => false,
                'error' => 'invalid.credentials',
                'msg' => 'USER IS NOT ADMIN'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ]);
    }
}
