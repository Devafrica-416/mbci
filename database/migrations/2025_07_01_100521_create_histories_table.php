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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // ex: affectation, changement_statut, cloture, etc.
            $table->string('entity_type'); // ex: Breakdown, Vehicle, Maintenance
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
