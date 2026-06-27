<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('awrel_settings')) {
            Schema::create('awrel_settings', function (Blueprint $table) {
                $table->id();
                $table->json('settings');
                $table->timestamps();
            });

            DB::table('awrel_settings')->insert([
                'settings' => \json_encode(config('awrel')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('awrel_settings');
    }
};
