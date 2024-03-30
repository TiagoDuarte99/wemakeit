<?php

namespace App\Http\Controllers;
/* 
use Illuminate\Support\Facades\Auth; */

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    //Login
    public function login(Request $request)
    {
        $credentials = $request->only(['name', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    // retorna o utilizador com o login
    public function me()
    {
        return response()->json(auth()->user());
    }

    //fazer logout
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
/*     public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
 */
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $ttlInMinutes = JWTAuth::factory()->getTTL();
    
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttlInMinutes * 40000000,
        ]);
    }
}