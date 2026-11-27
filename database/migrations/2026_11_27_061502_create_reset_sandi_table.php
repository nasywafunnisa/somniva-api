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
    Schema::create('reset_sandi', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('kode_hash');
        $table->timestamp('kadaluarsa_pada');
        $table->unsignedTinyInteger('percobaan')->default(0);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('reset_sandi');
}
};
