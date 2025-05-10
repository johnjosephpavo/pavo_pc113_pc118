<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            ['name' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'student', 'created_at' => $now, 'updated_at' =>$now]
        ];
        DB::table('roles')->insert($roles);
    }
}
