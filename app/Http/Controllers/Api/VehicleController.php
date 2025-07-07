<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/vehicles",
     *     summary="Liste des véhicules",
     *     tags={"Vehicle"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Liste des véhicules")
     * )
     */
    public function index(Request $request)
    {
        $vehicles = Vehicle::orderBy('marque')->orderBy('modele')->get();
        return response()->json($vehicles);
    }

    /**
     * @OA\Get(
     *     path="/api/vehicles/{id}",
     *     summary="Détail d'un véhicule",
     *     tags={"Vehicle"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détail du véhicule")
     * )
     */
    public function show($id)
    {
        $vehicle = Vehicle::with(['breakdowns', 'maintenances'])->findOrFail($id);
        return response()->json($vehicle);
    }
}
