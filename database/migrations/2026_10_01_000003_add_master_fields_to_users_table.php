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
            if (!Schema::hasColumn('users', 'study_program_id')) {
                $table->foreignId('study_program_id')->nullable()->constrained('study_programs')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'identifier_number')) {
                $table->string('identifier_number', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable();
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'study_program_id')) {
                $cols[] = 'study_program_id';
            }
            if (Schema::hasColumn('users', 'identifier_number')) {
                $cols[] = 'identifier_number';
            }
            if (Schema::hasColumn('users', 'phone')) {
                $cols[] = 'phone';
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $cols[] = 'is_active';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
