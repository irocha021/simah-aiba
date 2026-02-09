<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'toolsys',
                'email' => 'toolsys@toolsys.com',
                'password' => Hash::make('toolsys'),
                'phone' => null,
                'must_change_password' => false,
                'role' => 'root',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin SIMAH',
                'email' => 'root_admin@simah.com',
                'password' => Hash::make('simah'),
                'phone' => null,
                'must_change_password' => false,
                'role' => 'root',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('users')->whereIn('email', [
            'toolsys@toolsys.com',
            'root_admin@simah.com',
        ])->delete();
    }
};
