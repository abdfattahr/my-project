<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('supermarket_vendor');
    }

    public function down(): void
    {
        Schema::create('supermarket_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supermarket_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }
};
