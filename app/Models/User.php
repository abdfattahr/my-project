<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function supermarket()
    {
        return $this->hasOne(Supermarket::class, 'user_id');
    }


    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        if ($this->hasRole('vendor')) {
            return $this->supermarket !== null;
        }

        return false;
    }
}
