<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('panel_metadata', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->uuid('installation_id')->unique();
            $table->timestamp('config_updated_at');
            $table->timestamps();
        });

        $now = now();
        DB::table('panel_metadata')->insert([
            'id' => 1,
            'installation_id' => (string) Str::uuid(),
            'config_updated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_metadata');
    }
};
