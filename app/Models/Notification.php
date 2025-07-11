<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'titre',
        'message',
        'lu',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
