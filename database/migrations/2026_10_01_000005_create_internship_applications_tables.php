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
        // 1. Internship Applications
        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained('study_programs')->restrictOnDelete();
            $table->foreignId('partner_institution_id')->constrained('partner_institutions')->restrictOnDelete();
            $table->foreignId('internship_period_id')->constrained('internship_periods')->restrictOnDelete();
            
            // Student snapshot data for record consistency
            $table->string('student_name');
            $table->string('student_nim', 50)->index();
            $table->string('student_phone', 30);
            
            // Internship duration and details
            $table->date('start_date');
            $table->date('end_date');
            $table->string('proposal_title')->nullable();
            $table->text('internship_plan'); // Rencana magang / tugas magang
            
            // Workflow Status: DRAFT, DIAJUKAN, TIDAK_LENGKAP, LOLOS_TU, VERIFIKASI_KAPRODI, DISETUJUI, DITOLAK
            $table->string('status', 30)->default('DRAFT')->index();
            
            // Surat Pengantar (Generated / Issued by TU)
            $table->string('reference_letter_number')->nullable()->index();
            $table->string('reference_letter_path')->nullable();
            $table->timestamp('reference_letter_issued_at')->nullable();
            
            // Surat Balasan Instansi (Uploaded by Mahasiswa after reference letter is issued)
            $table->string('acceptance_letter_path')->nullable();
            $table->timestamp('acceptance_letter_uploaded_at')->nullable();
            
            // Rejection / Correction notes
            $table->text('review_notes')->nullable();
            
            $table->timestamps();

            // Compound indices for performant queue filtering
            $table->index(['status', 'study_program_id']);
            $table->index(['user_id', 'status']);
        });

        // 2. Internship Documents (Required & Optional attachments)
        Schema::create('internship_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->constrained('internship_applications')->cascadeOnDelete();
            $table->string('document_type', 50)->index(); // TRANSCRIPT, PROPOSAL, CV, PARENT_CONSENT, OTHER
            $table->string('document_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size'); // In bytes
            $table->string('mime_type', 100);
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['internship_application_id', 'document_type'], 'idx_internship_docs_app_type');
        });

        // 3. Internship Status Histories (Audit trail of every state transition)
        Schema::create('internship_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->constrained('internship_applications')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->index();
            $table->text('reason')->nullable(); // Mandatory on TIDAK_LENGKAP and DITOLAK
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['internship_application_id', 'created_at'], 'idx_status_hist_app_created');
        });

        // 4. App In-App Notifications
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Target user
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); // Action initiator
            $table->string('title');
            $table->text('message');
            $table->string('type', 50)->index(); // SUBMISSION, INCOMPLETE, APPROVAL, REJECTION, RESUBMISSION
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('internship_status_histories');
        Schema::dropIfExists('internship_documents');
        Schema::dropIfExists('internship_applications');
    }
};
