<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcategories', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->integer('external_id')->nullable();
            $table->foreignUuid('category_id')
                ->references('id')->on('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->integer('external_category_id')->nullable();
            $table->string('code');
            $table->string('name');
            $table->text('description');
            $table->smallInteger('expected_items_count')->nullable();
            $table->smallInteger('received_items_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcategories');
    }
};
