<?php

namespace App\Exceptions;

use Exception;

class NotEnoughBalanceException extends Exception
{
    protected $message = 'Not enough balance to make this booking';
}
