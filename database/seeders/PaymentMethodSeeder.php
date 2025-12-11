<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('Payment_Methods')->insert([
            ['name' => 'Syriatel Cash'],
            ['name' => 'MTN Cash'],
            ['name' => 'Bank Transfers'],
            ['name' => 'Cash'],
            ['name' => 'Credit/Debit Cards'],
        ]);
    }
}
