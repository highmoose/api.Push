<?php
// Create this file: app/Models/TaskModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TaskModel extends Model
{
    use HasFactory;

    protected $table = 'tasks'; // Matches your table name

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'due_date',
        'duration',
        'priority',
        'category',
        'status',
        'reminder',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'reminder' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    // 🧑 Relationships
    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    // 📅 Accessor for computed properties
    public function getIsOverdueAttribute()
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }
        return $this->due_date->isPast();
    }

    public function getDaysUntilDueAttribute()
    {
        if (!$this->due_date) {
            return null;
        }
        return $this->due_date->diffInDays(now(), false);
    }

    // 🔍 Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'completed');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today())
                    ->where('status', '!=', 'completed');
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->where('status', '!=', 'completed');
    }

    // 🛠️ Helper methods
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsInProgress()
    {
        $this->update([
            'status' => 'in-progress',
            'completed_at' => null,
        ]);
    }

    public function markAsPending()
    {
        $this->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->is_overdue;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in-progress';
    }

    protected function serializeDate($date)
    {
        // Return datetime in local format without timezone conversion
        return $date->format('Y-m-d\TH:i:s');
    }
}