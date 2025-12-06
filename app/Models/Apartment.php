<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\City;
use App\Models\User;
use App\Models\Rate;

class Apartment extends Model
{
    
    use HasFactory;

    // URLs
    public function getPhotoUrlAttribute()
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : null;
    }
    //

    // Relations
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function favouriteByUsers()
    {
        return $this->belongsToMany(User::class, 'favourite_apartments');
    }
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }
}
