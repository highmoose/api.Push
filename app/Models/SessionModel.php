<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionModel extends Model
{
    use HasFactory;

    protected $table = 'sessions'; // Matches your table name

    protected $fillable = [
        'trainer_id',
        'client_id',
        'gym_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'session_type',
        'location',
        'rate',
        'equipment_needed',
        'preparation_notes',
        'goals',
        'duration',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    // 🧑 Relationships
    public function trainer()
    {
        return $this->belongsTo(UserModel::class, 'trainer_id');
    }

    public function client()
    {
        return $this->belongsTo(UserModel::class, 'client_id');
    }

    public function sessionsAsTrainer()
    {
        return $this->hasMany(SessionModel::class, 'trainer_id');
    }

    public function sessionsAsClient()
    {
        return $this->hasMany(SessionModel::class, 'client_id');
    }

    protected function serializeDate($date)
    {
        // Return datetime in local format without timezone conversion
        return $date->format('Y-m-d\TH:i:s');
    }


    // public function gym()
    // {
    //     return $this->belongsTo(GymModel::class, 'gym_id');
    // }

    // // 🧠 Helpers
    // public function isUpcoming(): bool
    // {
    //     return $this->start_time->isFuture();
    // }

    // public function isPast(): bool
    // {
    //     return $this->end_time && $this->end_time->isPast();
    // }

    // public function isCancelled(): bool
    // {
    //     return $this->status === 'cancelled';
    // }

    // public function isCompleted(): bool
    // {
    //     return $this->status === 'completed';
    // }
}
