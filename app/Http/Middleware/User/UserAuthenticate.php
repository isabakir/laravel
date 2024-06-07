<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserAuthenticate
{
    /**
     * Handle an incoming request.
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token)
        {
            return response()->json([
              'message' => 'Token not provided'
            ], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (!$user)
        {
            return response()->json([
                'token' => null,
                'status' => false,
                'status_code' => 401,
                'error' => "Invalid token"
            ],401);

        }
        Auth::login($user);

        return $next($request);
    }
}