<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bot_user', function (Blueprint $table) {
            $table->unsignedBigInteger('bot_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['bot_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_user');
    }
};
