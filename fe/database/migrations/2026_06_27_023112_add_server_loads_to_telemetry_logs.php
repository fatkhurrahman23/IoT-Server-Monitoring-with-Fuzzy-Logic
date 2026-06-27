<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telemetry_logs', function (Blueprint $table) {
            $table->json('server_loads')->nullable()->after('cpu_load');
            $table->string('status')->nullable()->after('ac_target');
        });
    }

    public function down(): void
    {
        Schema::table('telemetry_logs', function (Blueprint $table) {
            $table->dropColumn(['server_loads', 'status']);
        });
    }
};
