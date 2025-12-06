<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class Governorate extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    // Relations
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
