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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
        });
        // Laravel default migration doesn't support easy enum changes for existing columns.
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('income', 'expense', 'savings')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
