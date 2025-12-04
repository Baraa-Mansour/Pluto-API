<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource\UserDetailResource;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getProfile(Request $request)
    {
        Log::info('User requested profile', ['user_id' => $request->user()->id]);

        $user = $this->userService->getProfile($request->user()->id);

        $user_resource = new UserDetailResource($user);

        return response()->json([
            'data' => $user_resource
        ], 200);
    }
}
