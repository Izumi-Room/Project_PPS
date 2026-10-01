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
        // 1. Course Conversions (Konversi MK)
        Schema::create('course_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->constrained('internship_applications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Mahasiswa
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete(); // Mata Kuliah
            $table->text('activity_plan'); // Rencana/relevansi kegiatan
            
            // Workflow status: DIAJUKAN, DISETUJUI_DOSEN_MK, DIVERIFIKASI_DOSBING, DISETUJUI_WADEK1, DIKETAHUI_KAPRODI, DITOLAK
            $table->string('status', 40)->default('DIAJUKAN')->index();
            $table->string('rejection_stage', 30)->nullable(); // DOSEN_MK, DOSBING, WADEK1
            $table->text('rejection_reason')->nullable();

            // Dosen MK stage
            $table->foreignId('dosen_mk_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dosen_mk_approved_at')->nullable();
            $table->text('dosen_mk_notes')->nullable();

            // Dosbing verification stage
            $table->foreignId('dosbing_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dosbing_verified_at')->nullable();
            $table->text('dosbing_notes')->nullable();

            // Wadek 1 approval stage
            $table->foreignId('wadek1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('wadek1_approved_at')->nullable();
            $table->text('wadek1_notes')->nullable();

            // Kaprodi acknowledgement stage
            $table->foreignId('kaprodi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('kaprodi_acknowledged_at')->nullable();
            $table->text('kaprodi_notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index(['internship_application_id', 'status']);
        });

        // 2. Conversion Status Histories (Audit trail)
        Schema::create('course_conversion_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_conversion_id')->constrained('course_conversions')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 50); // SUBMIT, RESUBMIT, APPROVE_DOSEN_MK, VERIFY_DOSBING, APPROVE_WADEK1, ACKNOWLEDGE_KAPRODI, REJECT
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['course_conversion_id', 'created_at'], 'idx_conv_hist_conv_created');
        });

        // 3. Internship Logbooks (Logbook Mingguan)
        Schema::create('internship_logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->constrained('internship_applications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Mahasiswa
            $table->unsignedInteger('week_number');
            $table->date('activity_date');
            $table->string('activity_title');
            $table->text('description');
            $table->string('attachment_path')->nullable();

            // Dosbing Review & Feedback
            $table->text('dosbing_feedback')->nullable();
            $table->timestamp('dosbing_reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['internship_application_id', 'week_number']);
            $table->index(['user_id', 'activity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_logbooks');
        Schema::dropIfExists('course_conversion_status_histories');
        Schema::dropIfExists('course_conversions');
    }
};
