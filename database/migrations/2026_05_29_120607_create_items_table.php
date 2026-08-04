<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->foreignUuid('project_id')
                ->references('id')->on('projects')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('category_id')
                ->references('id')->on('categories')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('subcategory_id')
                ->nullable()
                ->references('id')->on('subcategories')
                ->cascadeOnUpdate() ->nullOnDelete();
            $table->string('serial_number')->unique()->nullable();
            $table->string('code',36)->unique();
            $table->string('qr_code',128)->unique()->nullable();
            $table->foreignUuid('current_status_id')
                ->references('id')->on('statuses')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index('category_id');
            $table->index('subcategory_id');
            $table->index('code');
            $table->index('current_status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
