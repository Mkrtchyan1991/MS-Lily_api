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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->unique(); // produkt name muss sich nicht wiederholen
            $table->text('description')->nullable(); //nullable - kara datark mna
            $table->unsignedBigInteger('category_id'); // amboxjakan tiv
            $table->unsignedBigInteger('brand_id');     // amboxjakan tiv
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
