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
        // 1. Submission Components (Dibuat oleh Dosen MK secara dinamis)
        Schema::create('submission_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // Dosen MK
            $table->string('name'); // e.g. Laporan Akhir, Project Repository, Slide Presentasi
            $table->string('submission_type', 30)->default('FILE'); // FILE, LINK, FILE_OR_LINK, etc. (configurable, not hardcoded)
            $table->dateTime('deadline');
            $table->unsignedInteger('weight')->default(100); // Bobot (e.g. 20%, 30%, 50%)
            $table->boolean('is_required')->default(true);
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['course_id', 'is_active']);
            $table->index(['deadline', 'is_active']);
        });

        // 2. Student Submissions (Entri pengumpulan per mahasiswa per komponen)
        Schema::create('student_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_component_id')->constrained('submission_components')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Mahasiswa
            $table->foreignId('course_conversion_id')->constrained('course_conversions')->cascadeOnDelete();
            
            // Workflow status: BELUM_DIKUMPULKAN, DIKUMPULKAN, PERLU_PERBAIKAN, DIKIRIM_ULANG, DISETUJUI
            $table->string('status', 30)->default('BELUM_DIKUMPULKAN')->index();
            $table->unsignedInteger('current_version')->default(0);
            $table->decimal('score', 5, 2)->nullable();
            $table->dateTime('latest_submitted_at')->nullable();
            $table->text('latest_feedback')->nullable();
            
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'submission_component_id'], 'user_component_unique');
            $table->index(['course_conversion_id', 'status']);
        });

        // 3. Submission Versions (Menyimpan histori versi file & revisi tanpa menghapus file lama)
        Schema::create('submission_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_submission_id')->constrained('student_submissions')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('link_url', 1000)->nullable();
            $table->text('student_notes')->nullable();
            $table->dateTime('submitted_at')->useCurrent();
            
            // Status at this version: DIKUMPULKAN, PERLU_PERBAIKAN, DIKIRIM_ULANG, DISETUJUI
            $table->string('status', 30)->default('DIKUMPULKAN');
            $table->text('reviewer_feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['student_submission_id', 'version_number'], 'idx_sub_vers_sub_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_versions');
        Schema::dropIfExists('student_submissions');
        Schema::dropIfExists('submission_components');
    }
};
