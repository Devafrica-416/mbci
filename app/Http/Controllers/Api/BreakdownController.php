<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Breakdown;
use App\Models\BreakdownPhoto;
use Illuminate\Support\Facades\Storage;

class BreakdownController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/breakdowns",
     *     summary="Liste des pannes de l'utilisateur connecté",
     *     tags={"Breakdown"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Liste des pannes")
     * )
     */
    public function index(Request $request)
    {
        $breakdowns = Breakdown::with(['vehicle', 'photos'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();
        return response()->json($breakdowns);
    }

    /**
     * @OA\Post(
     *     path="/api/breakdowns",
     *     summary="Déclarer une nouvelle panne",
     *     tags={"Breakdown"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"vehicle_id","description"},
     *                 @OA\Property(property="vehicle_id", type="integer"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="lieu", type="string"),
     *                 @OA\Property(property="photos", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Panne créée")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'description' => 'required',
            'lieu' => 'nullable|string',
            'photos' => 'nullable',
            'photos.*' => 'image|max:4096',
        ]);
        $breakdown = Breakdown::create([
            'vehicle_id' => $request->vehicle_id,
            'user_id' => $request->user()->id,
            'description' => $request->description,
            'statut' => 'declaree',
            'date_declaration' => now(),
            'lieu' => $request->lieu,
        ]);
        // Enregistrement des photos si présentes
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('breakdown-photos', 'public');
                BreakdownPhoto::create([
                    'breakdown_id' => $breakdown->id,
                    'chemin_fichier' => $path,
                    'description' => null,
                ]);
            }
        }
        return response()->json($breakdown->load(['vehicle', 'photos']), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/breakdowns/{id}",
     *     summary="Détail d'une panne",
     *     tags={"Breakdown"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détail de la panne")
     * )
     */
    public function show($id)
    {
        $breakdown = Breakdown::with(['vehicle', 'photos'])->findOrFail($id);
        return response()->json($breakdown);
    }

    /**
     * @OA\Post(
     *     path="/api/breakdowns/{id}/photos",
     *     summary="Ajouter une photo à une panne",
     *     tags={"Breakdown"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"photo"},
     *                 @OA\Property(property="photo", type="string", format="binary"),
     *                 @OA\Property(property="description", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Photo ajoutée")
     * )
     */
    public function addPhoto(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|max:4096',
            'description' => 'nullable|string',
        ]);
        $breakdown = Breakdown::findOrFail($id);
        $path = $request->file('photo')->store('breakdown-photos', 'public');
        $photo = BreakdownPhoto::create([
            'breakdown_id' => $breakdown->id,
            'chemin_fichier' => $path,
            'description' => $request->description,
        ]);
        return response()->json($photo, 201);
    }
}
