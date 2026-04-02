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
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('type', ['income', 'expense', 'savings'])->default('expense')->after('name')->index();
        });
        
        \Illuminate\Support\Facades\DB::statement("UPDATE categories SET type = 'income' WHERE is_income = 1");
        \Illuminate\Support\Facades\DB::statement("UPDATE categories SET type = 'expense' WHERE is_income = 0");

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('is_income');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            //
        });
    }
};
