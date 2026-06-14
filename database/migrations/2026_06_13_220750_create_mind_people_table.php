<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mind_people', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mind_group_id')
                ->nullable()
                ->constrained('mind_groups')
                ->nullOnDelete();

            $table->string('name');

            $table->string('nickname')
                ->nullable();

            $table->string('photo')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mind_people');
    }
};
