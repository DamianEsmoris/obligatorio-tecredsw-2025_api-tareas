<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'email',
        'email_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'int',
            'email_verified_at' => 'datetime'
        ];
    }
}
