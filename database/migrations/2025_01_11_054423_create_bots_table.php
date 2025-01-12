<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('bot_id')->unique();
            $table->string('username');
            $table->timestamps();
        });


        DB::table('bots')->insert(
            DB::table('tasks')
                ->select([
                    'bot_id',
                    'username',
                ])
                ->distinct()
                ->get()
                ->map(fn (stdClass $task) => [
                    'id' => Str::uuid7(),
                    'bot_id' => $task->bot_id,
                    'username' => $task->username,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->toArray(),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
};
