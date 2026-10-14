<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function isGudang(): bool
    {
        return $this->role === 'gudang';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function incomingTransactions()
    {
        return $this->hasMany(IncomingTransaction::class);
    }

    public function outgoingTransactions()
    {
        return $this->hasMany(OutgoingTransaction::class);
    }
}
