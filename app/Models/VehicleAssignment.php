<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleAssignment extends Model
{
    protected $fillable = [
        'vehicle_id',
        'garage_id',
        'assigned_by',
        'statut',
        'date_affectation',
        'date_sortie',
        'commentaire',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function garage()
    {
        return $this->belongsTo(Garage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
