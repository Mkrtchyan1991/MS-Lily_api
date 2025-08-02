<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('textDark')->nullable();
            $table->decimal('del', 10, 2)->nullable(); 
            $table->decimal('textSuccess', 10, 2)->nullable(); 
            $table->decimal('star', 3, 2)->nullable();     });
    }
    
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['textDark', 'del', 'textSuccess', 'star']);
        });
    }
};
