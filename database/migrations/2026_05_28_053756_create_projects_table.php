<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->string('external_id')->nullable()->unique();
            $table->foreignUuid('pic_id')
                ->nullable()
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('external_pic_id')->nullable();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('year');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
