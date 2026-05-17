<?php

use App\Models\Admin;
use App\Support\AdminPanelPasswordValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('it accepts a hashed admin password', function () {
    $admin = Admin::query()->create([
        'name' => 'Hashed Admin',
        'email' => 'hashed-admin@example.com',
        'phone' => '01000000001',
        'password' => 'secret-password',
        'status' => 'active',
    ]);

    $validator = app(AdminPanelPasswordValidator::class);

    expect($validator->validate($admin, 'secret-password'))->toBeTrue();
    expect(Hash::isHashed((string) $admin->fresh()->password))->toBeTrue();
});

test('it upgrades a legacy plain text admin password after a successful check', function () {
    DB::table('admins')->insert([
        'name' => 'Legacy Admin',
        'email' => 'legacy-admin@example.com',
        'phone' => '01000000002',
        'password' => 'plain-secret',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = Admin::query()->where('email', 'legacy-admin@example.com')->firstOrFail();
    $validator = app(AdminPanelPasswordValidator::class);

    expect($validator->validate($admin, 'plain-secret'))->toBeTrue();
    expect($admin->fresh()->password)->not->toBe('plain-secret');
    expect(Hash::check('plain-secret', (string) $admin->fresh()->password))->toBeTrue();
});

test('it rejects an invalid admin password without mutating the stored value', function () {
    DB::table('admins')->insert([
        'name' => 'Legacy Admin',
        'email' => 'legacy-admin-invalid@example.com',
        'phone' => '01000000003',
        'password' => 'plain-secret',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = Admin::query()->where('email', 'legacy-admin-invalid@example.com')->firstOrFail();
    $validator = app(AdminPanelPasswordValidator::class);

    expect($validator->validate($admin, 'wrong-password'))->toBeFalse();
    expect($admin->fresh()->password)->toBe('plain-secret');
});
