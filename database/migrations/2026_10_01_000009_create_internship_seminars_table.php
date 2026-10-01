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
        Schema::create('internship_seminars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->constrained('internship_applications')->cascadeOnDelete();
            $table->foreignId('course_conversion_id')->nullable()->constrained('course_conversions')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // student
            $table->foreignId('dosen_mk_id')->constrained('users')->cascadeOnDelete(); // Dosen MK who determined/scheduled

            $table->boolean('is_required')->default(true);
            $table->string('status', 30)->default('MENUNGGU_KEPUTUSAN');
            // MENUNGGU_KEPUTUSAN, TIDAK_DIPERLUKAN, TERJADWAL, DIKETAHUI, DISETUJUI, DILAKSANAKAN, DINILAI, DITOLAK

            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time', 50)->nullable();
            $table->string('location_or_link')->nullable();
            $table->text('information')->nullable();

            // Dosbing acknowledgement
            $table->foreignId('dosbing_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dosbing_acknowledged_at')->nullable();
            $table->text('dosbing_notes')->nullable();

            // Kaprodi acknowledgement
            $table->foreignId('kaprodi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('kaprodi_acknowledged_at')->nullable();
            $table->text('kaprodi_notes')->nullable();

            // Wadek 1 approval / rejection
            $table->foreignId('wadek1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('wadek1_approved_at')->nullable();
            $table->timestamp('wadek1_rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Execution / rescheduling tracking
            $table->timestamp('conducted_at')->nullable();
            $table->timestamp('rescheduled_at')->nullable();
            $table->unsignedInteger('reschedule_count')->default(0);

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['dosen_mk_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_seminars');
    }
};
