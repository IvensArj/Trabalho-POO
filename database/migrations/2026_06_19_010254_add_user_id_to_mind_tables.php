<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        
        DB::table('mind_group_mind_person')->truncate();
        DB::table('mind_people')->truncate();
        DB::table('mind_groups')->truncate();

        Schema::enableForeignKeyConstraints();

        Schema::table('mind_people', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->index('user_id');
        });

        Schema::table('mind_groups', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('mind_people', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('mind_groups', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
