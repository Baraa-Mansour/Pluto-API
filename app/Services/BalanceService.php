<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\NotEnoughBalanceException;
use App\Repositories\UserRepo;

class BalanceService
{
    private UserRepo $userRepo;

    public function __construct(UserRepo $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function deduct(User $user, float $amount)
    {
        if ($user->balance < $amount) 
        {
            throw new NotEnoughBalanceException();
        }

        $user->balance -= $amount;
        $this->userRepo->updateUser($user);
    }

    public function increase(User $user, float $amount)
    {
        $user->balance += $amount;
        $this->userRepo->updateUser($user);
    }
}

