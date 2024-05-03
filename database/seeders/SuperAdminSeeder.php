<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => Str::uuid(),
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Ganti dengan password yang diinginkan
            'role' => 'superadmin',
            'key' => Str::random(16), // Jika Anda memiliki logika tertentu untuk 'key', ganti ini
            'total_tokens' => 0, // Boleh diubah sesuai kebutuhan
            'phone_number' => null, // Nomor telepon opsional
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
