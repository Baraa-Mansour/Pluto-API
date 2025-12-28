<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->date('from_date');
            $table->date('to_date');
            //$table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->decimal('total_price');
            $table->enum('status' , [
                Booking::STATUS_ACCEPTED, 
                Booking::STATUS_CANCELED, 
                Booking::STATUS_DECLINED,
                Booking::STATUS_REGISTERED
            ])->default(Booking::STATUS_REGISTERED);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
