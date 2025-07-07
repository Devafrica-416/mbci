<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Breakdown extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'description',
        'statut',
        'date_declaration',
        'lieu',
        'garage_id',
        'cout',
        'date_fin',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(BreakdownPhoto::class);
    }

    public function garage()
    {
        return $this->belongsTo(Garage::class);
    }
}
