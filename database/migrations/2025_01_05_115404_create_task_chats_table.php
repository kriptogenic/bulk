<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_chats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('task_id')->constrained();
            $table->bigInteger('chat_id');
            $table->string('status')->nullable();
            $table->string('prefetch_status')->nullable();
            $table->timestamps();

            $table->unique(['task_id', 'chat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_chats');
    }
};
