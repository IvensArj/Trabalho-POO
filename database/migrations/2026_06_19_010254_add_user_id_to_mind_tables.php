<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verifica se a coluna user_id existe em mind_people
        if (!Schema::hasColumn('mind_people', 'user_id')) {
            Schema::table('mind_people', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            });
        }

        // Se houver outras tabelas (mind_groups, etc.), faça o mesmo
        if (!Schema::hasColumn('mind_groups', 'user_id')) {
            Schema::table('mind_groups', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Opcional: remover as colunas
        Schema::table('mind_people', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('mind_groups', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};