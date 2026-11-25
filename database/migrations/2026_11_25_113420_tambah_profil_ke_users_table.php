<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('peran', 20)->default('pengguna')->after('password');
        $table->unsignedTinyInteger('target_tidur_jam')->default(8)->after('peran');
        $table->string('jam_bangun', 5)->default('06:30')->after('target_tidur_jam');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['peran', 'target_tidur_jam', 'jam_bangun']);
    });
}
};
