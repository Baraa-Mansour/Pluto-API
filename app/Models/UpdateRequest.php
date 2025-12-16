<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateRequest extends Model
{
    
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    protected function casts(): array
    {
        return [
            'new_from_date' => 'date',
            'new_to_date' => 'date',
        ];
    }
}
