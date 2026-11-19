<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@mamamia.test'],
            [
                'name' => 'Admin Mamamia',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // 2 Primary Merchants
        User::firstOrCreate(
            ['email' => 'merchant1@mamamia.test'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'merchant2@mamamia.test'],
            [
                'name' => 'Siti Aminah',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'status' => 'active',
            ]
        );

        // Additional merchants for demo restaurants
        for ($i = 3; $i <= 20; $i++) {
            User::firstOrCreate(
                ['email' => "merchant{$i}@mamamia.test"],
                [
                    'name' => "Pemilik Toko {$i}",
                    'password' => Hash::make('password'),
                    'role' => 'merchant',
                    'status' => 'active',
                ]
            );
        }

        // 5 Active Users
        $userNames = [
            'Rani Rahmawati',
            'Dimas Pratama',
            'Anisa Putri',
            'Fajar Nugroho',
            'Maya Kusuma',
        ];

        foreach ($userNames as $index => $name) {
            $num = $index + 1;
            User::firstOrCreate(
                ['email' => "user{$num}@mamamia.test"],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'status' => 'active',
                ]
            );
        }

        // 1 Banned User for testing
        User::firstOrCreate(
            ['email' => 'banned@mamamia.test'],
            [
                'name' => 'Pengguna Diblokir',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'banned',
                'banned_reason' => 'Melakukan spam dan pelanggaran kebijakan komunitas berkali-kali.',
            ]
        );
    }
}
