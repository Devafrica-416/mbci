<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'user_id',
        'old_values',
        'new_values',
        'comment',
    ];

    public function entity()
    {
        return $this->morphTo(null, 'entity_type', 'entity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
