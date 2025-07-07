<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $fillable = [
        'vehicle_id',
        'garage_id',
        'type',
        'cout',
        'date_debut',
        'date_fin',
        'description',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function garage()
    {
        return $this->belongsTo(Garage::class);
    }
}
