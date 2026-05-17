<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminPanelPasswordValidator
{
    public function validate(Admin $admin, string $password): bool
    {
        $storedPassword = (string) $admin->getAuthPassword();

        if ($storedPassword === '') {
            return false;
        }

        if (Hash::isHashed($storedPassword)) {
            if (! Hash::check($password, $storedPassword)) {
                return false;
            }

            if (Hash::needsRehash($storedPassword)) {
                $admin->forceFill([
                    'password' => $password,
                ])->saveQuietly();
            }

            return true;
        }

        if (! hash_equals($storedPassword, $password)) {
            return false;
        }

        $admin->forceFill([
            'password' => $password,
        ])->saveQuietly();

        return true;
    }
}
