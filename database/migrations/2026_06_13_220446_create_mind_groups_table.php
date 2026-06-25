<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mind_groups', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // Slug não tem unique aqui — a unicidade real é (user_id, name)
            // e o índice composto é adicionado em migration posterior.
            // O app gera sufixo aleatório para evitar colisão entre usuários.
            $table->string('slug');

            $table->text('description')
                ->nullable();

            $table->string('icon')
                ->default('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mind_groups');
    }
};
