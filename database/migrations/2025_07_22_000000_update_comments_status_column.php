<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status column and update existing data
        Schema::table('comments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('content');
        });

        // Migrate existing data: approved=true -> status='approved', approved=false -> status='pending'
        DB::statement("UPDATE comments SET status = CASE WHEN approved = 1 THEN 'approved' ELSE 'pending' END");

        // Remove the old approved column
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back approved column
        Schema::table('comments', function (Blueprint $table) {
            $table->boolean('approved')->default(false)->after('content');
        });

        // Migrate data back: status='approved' -> approved=true, else -> approved=false
        DB::statement("UPDATE comments SET approved = CASE WHEN status = 'approved' THEN 1 ELSE 0 END");

        // Remove status column
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};