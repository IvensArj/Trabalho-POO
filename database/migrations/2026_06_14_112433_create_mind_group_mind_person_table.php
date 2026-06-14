<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mind_group_mind_person', function (Blueprint $table) {

            $table->id();

            $table->foreignId('mind_group_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('mind_person_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mind_group_mind_person');
    }
};