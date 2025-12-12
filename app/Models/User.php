<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Apartment;
use App\Models\Rate;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens;

    // Roles
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    //

    // Statuses
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REGISTERED = 'registered';
    public const STATUS_DELETED = 'deleted';
    //

    // URLs
    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image
            ? asset('storage/' . $this->profile_image)
            : null;
    }

    public function getIdImageUrlAttribute()
    {
        return $this->id_image
            ? asset('storage/' . $this->id_image)
            : null;
    }
    //

    // Relations  
    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }
    public function favouriteApartments()
    {
        return $this->belongsToMany(Apartment::class, 'favourite_apartments');
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'password' => 'hashed',
        ];
    }

}
