<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'rol',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Método de arranque del modelo.
     * Vrifica su no hay usuario para asiganr rol administrador
     * En caso contrario asigna rol usuario
     * Y se hace Hash al password
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->rol = User::count() === 0 ? 1 : 2;
        });
    }

    /**
    * Verifica si la cuenta del usuario está activada.
    *
    * @return bool
    */
    public function isActivated()
    {
        return $this->status;
    }

    public function roles()
    {
        return $this->belongsTo(Roles::class, 'rol');
    }
}
