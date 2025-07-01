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
        Schema::create('advertisement_supermarkets', function (Blueprint $table) {
            $table->id();
            $table->date('date_publication');
            $table->string('image')->nullable();
            $table->string('information',100);
            $table->foreignId('supermarket_id')->constrained('supermarkets')->onDelete('cascade');
            $table->foreignId('advertisement_id')->constrained('advertisements')->onDelete('cascade');            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_supermarkets');
    }
};
