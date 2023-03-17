<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Providers\JwtProvider;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Get user credentials from request
        $credentials = $request->only('email', 'password');

        // Authenticate user
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Generate JWT token for user
        $user = User::where('email', $request->email)->first();
        $payload = [
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + (60 * 60),
        ];
        $jwtToken = JwtProvider::encode($payload);

        return response()->json(['token' => $jwtToken]);
    }

    public function register(Request $request)
    {

        $user = new User;
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->password = bcrypt('password');
        $user->save();

        return "user created successfully";
    }
}
