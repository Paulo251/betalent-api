<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GatewaySeeder extends Seeder
{
    public function run(): void
    {
        DB::table("gateways")->insert([
            [
                "name" => "Gateway1",
                "is_active" => true,
                "priority" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Gateway2",
                "is_active" => true,
                "priority" => 2,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
