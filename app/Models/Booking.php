<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    // Status
    public const STATUS_REGISTERED = 'registered';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_CANCELED = 'canceled';
    //

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
        ];
    }
}
