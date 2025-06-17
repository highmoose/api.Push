<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class messageModel extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public $timestamps = true;

    public function sender()
    {
        return $this->belongsTo(UserModel::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(UserModel::class, 'receiver_id');
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
