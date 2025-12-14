<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserRepo
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function saveUser(User $userData) : bool
    {
        Log::info('UserRepo saveUser called for phone: ' . $userData['phone']);

        $isSaved = $userData->save();

        Log::info('UserRepo saveUser ' . ($isSaved ? 'success' : 'failed') . ' for phone: ' . $userData['phone']);

        return $isSaved;
    }

    public function updateUser(User $userData) : bool
    {
        Log::info('UserRepo updateUser called for id: ' . $userData->id);

        $isUpdated = $userData->save();

        Log::info('UserRepo updateUser ' . ($isUpdated ? 'success' : 'failed') . ' for id: ' . $userData->id);

        return $isUpdated;
    }

    // This delete row from db
    public function deleteUserById(int $id) : bool
    {
        Log::info('UserRepo deleteUserById called for id: ' . $id);

        $isDeleted = User::where('id', $id)->delete();

        Log::info('UserRepo deleteUserById ' . ($isDeleted ? 'success' : 'failed') . ' for id: ' . $id);

        return (bool)$isDeleted;
    }

    public function softDeleteUser(int $id)
    {
        Log::info('UserRepo softDeleteUser called for id: ' . $id);

        $user = User::findOrFail($id);
        $user->status = User::STATUS_DELETED;

        $isDeleted = $user->save();

        Log::info('UserRepo softDeleteUser ' . ($isDeleted ? 'success' : 'failed') . ' for id: ' . $id);

        return (bool)$isDeleted;
    }

    public function findByPhone(string $phone): ?User
    {
        Log::info('UserRepo findByPhone called for phone: ' . $phone);

        $user = User::where('phone', $phone)->where('status', User::STATUS_ACTIVE)->first(); // check if phone exists and user is active

        if ($user) 
        {
            Log::info('UserRepo findByPhone found user for phone: ' . $phone);
        } 
        else 
        {
            Log::info('UserRepo findByPhone did not find user for phone: ' . $phone);
        }

        return $user;
    }

    public function findUserById(int $id) : User
    {
        Log::info('UserRepo findUserById called for id: ' . $id);

        $user = User::findOrFail($id);

        if ($user) 
        {
            Log::info('UserRepo findUserById found user for id: ' . $id);
        } 
        else 
        {
            Log::info('UserRepo findUserById did not find user for id: ' . $id);
        }

        return $user;
    }

    public function userExists(int $id) : bool
    {
        Log::info('UserRepo userExists called for id: ' . $id);

        $exists = User::where('id', $id)->exists();

        Log::info('UserRepo userExists ' . ($exists ? 'found' : 'not found') . ' for id: ' . $id);

        return $exists;
    }

    
    public function getRegisters()
    {
        Log::info('UserRepo getRegisters called');

        $users = User::where('status' , User::STATUS_REGISTERED)->get();

        Log::info('UserRepo getRegisters retrieved ' . $users->count() . ' users');

        return $users;
    }

    public function getUsers()
    {
        Log::info('UserRepo getAllUsers called');

        $users = User::whereNotIn('status', [User::STATUS_REGISTERED, User::STATUS_DELETED])->get();

        Log::info('UserRepo getAllUsers retrieved ' . $users->count() . ' users');

        return $users;
    }

    public function getOwners()
    {
        Log::info('UserRepo getAllOwners called');

        $users = User::whereHas('apartments', function ($query) {
            $query->where('status', '!=', User::STATUS_DELETED)->where('status', '!=', User::STATUS_REGISTERED); // dont need register but for test
        })->get();

        Log::info('UserRepo getAllOwners retrieved ' . $users->count() . ' users');

        return $users;
    }

    
    // for report but need optimization if have time
    public function countUsers() : int
    {
        Log::info('UserRepo countUsers called');

        $count = User::whereNotIn('status', [User::STATUS_REGISTERED, User::STATUS_DELETED])->count();

        Log::info('UserRepo countUsers found ' . $count . ' users');

        return $count;
    }
    public function countOwners() : int
    {
        Log::info('UserRepo countOwners called');

        $count = User::whereHas('apartments', function ($query) {
            $query->where('status', '!=', User::STATUS_DELETED)->where('status', '!=', User::STATUS_REGISTERED); // dont need register but for test
        })->count();

        Log::info('UserRepo countOwners found ' . $count . ' owners');

        return $count;
    }   
    public function countRegisters() : int
    {
        Log::info('UserRepo countRegisters called');

        $count = User::where('status' , User::STATUS_REGISTERED)->count();

        Log::info('UserRepo countRegisters found ' . $count . ' registers');

        return $count;
    }

}
