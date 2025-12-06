<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Governorate;
use App\Models\Apartment;

class City extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    // Relations
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }
}
