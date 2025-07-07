<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('marque');
            $table->string('modele');
            $table->string('immatriculation')->unique();
            $table->enum('statut', ['disponible', 'en_panne', 'en_reparation', 'en_maintenance'])->default('disponible');
            $table->date('date_mise_en_service')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
