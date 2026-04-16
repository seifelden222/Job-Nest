<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'email',
        'code',
        'type',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user()
    {
        if ($this->user_type !== 'user') {
            return null;
        }

        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        if ($this->user_type !== 'admin') {
            return null;
        }

        return $this->belongsTo(Admin::class, 'user_id');
    }
}
