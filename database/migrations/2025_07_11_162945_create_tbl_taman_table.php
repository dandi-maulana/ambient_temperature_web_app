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
        Schema::create('tbl_taman', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('id_perangkat', 50);
            $table->string('nilai_temperatur', 50);
            $table->datetime('tanggal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_taman');
    }
};