<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Apartment extends Model
{
    
    use HasFactory;


    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function favouriteByUsers()
    {
        return $this->belongsToMany(User::class, 'favourite_apartments');
    }
}
