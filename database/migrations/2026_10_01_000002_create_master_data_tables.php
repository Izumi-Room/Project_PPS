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
        // 1. Program Studi (Study Programs)
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name')->index();
            $table->string('degree_level', 10)->default('S1'); // D3, D4, S1, S2
            $table->string('faculty')->default('Fakultas Teknik');
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
            $table->index(['name', 'is_active']);
        });

        // 2. Instansi Mitra (Partner Institutions)
        Schema::create('partner_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->text('address');
            $table->string('contact_person')->nullable(); // Nama kontak / PIC
            $table->string('email')->index();
            $table->string('phone', 30)->nullable();
            $table->string('website')->nullable();
            $table->string('sector', 50)->default('Swasta'); // BUMN, Swasta, Pemerintah, Startup, LSM
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['name', 'email']);
            $table->index(['sector', 'is_active']);
        });

        // 3. Mata Kuliah (Courses)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_program_id')->constrained('study_programs')->cascadeOnDelete();
            $table->string('code', 20)->index();
            $table->string('name')->index();
            $table->unsignedTinyInteger('credits')->default(2); // SKS
            $table->unsignedTinyInteger('semester')->default(6)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['code', 'study_program_id']);
            $table->index(['study_program_id', 'semester', 'is_active']);
        });

        // 4. Periode Magang (Internship Periods)
        Schema::create('internship_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year', 20)->index(); // e.g. 2026/2027
            $table->string('semester_type', 10)->default('GENAP'); // GANJIL, GENAP
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->boolean('is_active')->default(false)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['academic_year', 'semester_type']);
            $table->index(['academic_year', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
        Schema::dropIfExists('internship_periods');
        Schema::dropIfExists('partner_institutions');
        Schema::dropIfExists('study_programs');
    }
};
