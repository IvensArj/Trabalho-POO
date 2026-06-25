<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Garante que um mesmo usuário não tenha dois grupos com o mesmo nome.
     * O índice único global do slug foi removido — o slug agora é derivado
     * (com sufixo aleatório) e a unicidade do grupo passa a ser por (user_id, name).
     */
    public function up(): void
    {
    Schema::table('mind_groups', function (Blueprint $table) {
        // Só remove se existir — evita erro em ambientes onde o índice
        // nunca foi criado (ex: Codespaces, DB fresco).
        if (Schema::hasIndex('mind_groups', 'mind_groups_slug_unique')) {
            $table->dropUnique(['slug']);
        }

        $table->unique(['user_id', 'name'], 'mind_groups_user_id_name_unique');
    });

    Schema::table('mind_events', function (Blueprint $table) {
        $table->index(['user_id', 'date'], 'mind_events_user_id_date_index');
    });
    }


    public function down(): void
    {
        Schema::table('mind_events', function (Blueprint $table) {
            if (Schema::hasIndex('mind_events', 'mind_events_user_id_date_index')) {
                $table->dropIndex('mind_events_user_id_date_index');
            }
        });

        Schema::table('mind_groups', function (Blueprint $table) {
            if (Schema::hasIndex('mind_groups', 'mind_groups_user_id_name_unique')) {
                $table->dropUnique('mind_groups_user_id_name_unique');
            }

            if (!Schema::hasIndex('mind_groups', 'mind_groups_slug_unique')) {
                $table->unique('slug');
            }
        });
    }
};