<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_status_attachments', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->foreignUuid('item_status_id')
                ->references('id')->on('item_status')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('item_status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_status_attachments');
    }
};
