<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profile_photo',
        'status',
        'last_login_at',
    ];
}
