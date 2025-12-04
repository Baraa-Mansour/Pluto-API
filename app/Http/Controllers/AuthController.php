<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest\LoginRequest;
use App\Http\Requests\AuthRequest\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;


class AuthController extends Controller
{
    
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    public function register(RegisterRequest $request) 
    {
        
        $validatedData = $request->validated();

        Log::info('controller register called for phone: ' . $validatedData['phone']);

        $isRegistered = $this->authService->registerUser($validatedData);

        Log::info('controller register ' . ($isRegistered ? 'success' : 'failed') . ' for phone: ' . $validatedData['phone']);

        if($isRegistered)
        {
            return response()->json([
                'message' => 'User registered successfully.'
            ] , 201);
        }
    }

    public function login(LoginRequest $request) 
    {
        $validatedData = $request->validated();

        Log::info('controller login called for phone: ' . $validatedData['phone']);

        $user = $this->authService->loginUser($validatedData['phone'], $validatedData['password']);

        Log::info('controller login ' . ($user ? 'succeeded' : 'failed') . ' for phone: ' . $validatedData['phone']);

        if($user)
        {
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'token' => $token
            ] , 200);
        }

        return response()->json([
            'message' => 'Invalid phone or password.'
        ] , 401);
        
    }

    public function logout(Request $request) 
    {
        Log::info('controller logout called for user id: ' . $request->user()->id);
        
        /** @var PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        Log::info('controller logout succeeded for user id: ' . $request->user()->id . ' and token id: ' . $token->id);

        return response()->json([
            'message' => 'Logout successful.'
        ], 200);
    }

}
