<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_status', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->foreignUuid('item_id')
                ->references('id')->on('items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('status_id')
                ->references('id')->on('statuses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('previous_status_id')
                ->nullable()
                ->references('id')->on('statuses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('updated_by')
                ->nullable()
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('location_name')->nullable();
            $table->text('location_address')->nullable();
            $table->timestamp('effective_at');
            $table->timestamp('ineffective_at')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_status');
    }
};
