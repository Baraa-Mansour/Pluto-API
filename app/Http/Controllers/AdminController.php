<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Http\Resources\UserResource\UserBasicInfoResource;
use App\Http\Resources\UserResource\UserRegisterResource;
use App\Services\BalanceService;
use App\Http\Requests\AdminRequest\AddBalanceRequest;
use App\Models\User;

class AdminController extends Controller
{
    private UserService $userService;
    private BalanceService $BalanceService;

    public function __construct(UserService $userService, BalanceService $BalanceService)
    {
        $this->userService = $userService;
        $this->BalanceService = $BalanceService;
    }

    
    public function getAllRegisters(Request $request)
    {
        Log::info('Admin requested all user registers', ['admin_id' => $request->user()->id]);

        $users = $this->userService->getAllRegisters();

        $users_resource = UserRegisterResource::collection($users);

        return response()->json([
            'data' => $users_resource
        ], 200);
    }

    public function getAllUsers(Request $request)
    {
        Log::info('Admin requested all users', ['admin_id' => $request->user()->id]);

        $users = $this->userService->getAllUsers();

        $users_resource = UserBasicInfoResource::collection($users);

        return response()->json([
            'data' => $users_resource
        ], 200);
    }

    public function getAllOwners(Request $request)
    {
        Log::info('Admin requested all owners', ['admin_id' => $request->user()->id]);

        $users = $this->userService->getAllOwners();

        $users_resource = UserBasicInfoResource::collection($users);

        return response()->json([
            'data' => $users_resource
        ], 200);
    }

    public function getReport(Request $request)
    {
        Log::info('Admin requested user report', ['admin_id' => $request->user()->id]);

        $report = $this->userService->generateReport();

        return response()->json([
            'data' => $report
        ], 200);
    }


    public function deleteRegister(Request $request,int $id)
    {
        Log::info('Admin requested to delete a user register', ['admin_id' => $request->user()->id, 'register_id' => $id]);

        $this->userService->deleteRegister($id);

        return response()->json([
            'message' => 'User register deleted successfully'
        ], 200);
    }

    public function acceptRegister(Request $request,int $id)
    {
        Log::info('Admin requested to accept a user register', ['admin_id' => $request->user()->id, 'register_id' => $id]);

        $this->userService->acceptRegister($id);

        return response()->json([
            'message' => 'User register accepted successfully'
        ], 200);
    }

    public function addWalletBalance(AddBalanceRequest $request)
    {
        $validatedData = $request->validated();
        $userId = $validatedData['user_id'];

        $user = User::findOrFail($userId);

        $amount = $validatedData['amount'];

        $this->BalanceService->increase($user, $amount);

        return response()->json([
            'message' => 'Balance added successfully'
        ], 200);
    }


    public function deleteUser(int $id)
    {
        Log::info('AdminController deleteUser called');

        $user = User::where('id', $id)->where('status', '!=', User::STATUS_DELETED)->firstOrFail();

        $isDeleted = $this->userService->softDeleteUserWithFutureBookingsAndTokens($user);

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }
}
