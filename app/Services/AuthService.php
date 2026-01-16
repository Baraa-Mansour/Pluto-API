<?php

namespace App\Services;

use App\Repositories\UserRepo;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthService
{
    private UserRepo $userRepo;

    public function __construct(UserRepo $userRepo)
    {
        $this->userRepo = $userRepo;
    }


    public function registerUser(array $userData): bool
    {
        Log::info('service registerUser called for phone: ' . $userData['phone']);

        $user = $this->getUserInstanceFromData($userData);

        $isSaved = $this->userRepo->saveUser($user);

        Log::info('service registerUser ' . ($isSaved ? 'succeeded' : 'failed') . ' for phone: ' . $userData['phone']);

        return $isSaved;
    }

    public function loginUser(array $validatedData): ?User
    {
        Log::info('service loginUser called for phone: ' . $validatedData['phone']);

        $user = $this->userRepo->findByPhone($validatedData['phone']);

        if ($user && password_verify($validatedData['password'], $user->password)) 
        {
            Log::info('service loginUser succeeded for phone: ' . $validatedData['phone']);
            if(isset($validatedData['fcm_token'])) 
            {
                $user->fcm_token = $validatedData['fcm_token'];
                $this->userRepo->saveUser($user);
            }
            return $user;
        }

        Log::info('service loginUser failed for phone: ' . $validatedData['phone']);

        return null;
    }

    // Create new user instance from data after hash pass and save images(take the path).
    private function getUserInstanceFromData(array $userData): User
    {
        Log::info('service getUserInstanceFromData called for phone: ' . $userData['phone']);

        $user = new User();
        
        $user->first_name = $userData['first_name'];
        $user->last_name = $userData['last_name'];
        $user->birth_date = $userData['birth_date'];
        $user->phone = $userData['phone'];
        $user->password = $userData['password']; // Hash the password in the model

        $user->profile_image = $userData['profile_image']->store('profile_images', 'public'); // Store image and get path
        $user->id_image = $userData['id_image']->store('id_images', 'public'); // Store image and get path

        $user->role = User::ROLE_USER;
        $user->status = User::STATUS_REGISTERED;
        $user->balance = 500000;

        Log::info('service getUserInstanceFromData succeeded for phone: ' . $userData['phone']);

        return $user;
    }
}
