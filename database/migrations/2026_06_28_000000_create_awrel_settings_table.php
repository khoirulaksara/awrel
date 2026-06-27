<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('awrel_settings');
    }
};
