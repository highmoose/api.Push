<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'is_temp',
        'phone',
        'location',
        'gym',
        'date_of_birth',
        'gym_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    public function clients()
    {
        return $this->belongsToMany(User::class, 'trainer_client', 'trainer_id', 'client_id')->withTimestamps();
    }

    public function trainers()
    {
        return $this->belongsToMany(User::class, 'trainer_client', 'client_id', 'trainer_id')->withTimestamps();
    }

    // public function gym()
    // {
    //     return $this->belongsTo(Gym::class);
    // }

    // Optional: Convenience accessor for full name
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    
}
