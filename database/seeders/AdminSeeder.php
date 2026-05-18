<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            [
                'name' => 'JobNest Super Admin',
                'email' => 'admin@jobnest.test',
                'phone' => '01030030001',
                'password' => 'password',
                'status' => 'active',
            ],
            [
                'name' => 'Operations Admin',
                'email' => 'ops@jobnest.test',
                'phone' => '01030030002',
                'password' => 'password',
                'status' => 'active',
            ],
            [
                'name' => 'Support Admin',
                'email' => 'support@jobnest.test',
                'phone' => '01030030003',
                'password' => 'password',
                'status' => 'active',
            ],
        ])->each(function (array $admin): void {
            Admin::query()->updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'phone' => $admin['phone'],
                    'password' => Hash::make($admin['password']),
                    'status' => $admin['status'],
                ]
            );
        });
    }
}
