<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakdownPhoto extends Model
{
    protected $fillable = [
        'breakdown_id',
        'chemin_fichier',
        'description',
    ];

    public function breakdown()
    {
        return $this->belongsTo(Breakdown::class);
    }
}
