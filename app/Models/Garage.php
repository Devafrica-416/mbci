<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Garage extends Model
{
    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
    ];

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
}
