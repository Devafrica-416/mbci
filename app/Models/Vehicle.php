<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'marque',
        'modele',
        'immatriculation',
        'statut',
        'date_mise_en_service',
        'garage_id',
    ];

    public function breakdowns()
    {
        return $this->hasMany(Breakdown::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function garage()
    {
        return $this->belongsTo(Garage::class);
    }

    public function assignments()
    {
        return $this->hasMany(\App\Models\VehicleAssignment::class);
    }

    public function histories()
    {
        return $this->hasMany(\App\Models\History::class, 'entity_id')->where('entity_type', 'Vehicle');
    }
}
