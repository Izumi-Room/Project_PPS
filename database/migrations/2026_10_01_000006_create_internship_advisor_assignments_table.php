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
        // Add advisor fields to internship_applications
        Schema::table('internship_applications', function (Blueprint $table) {
            $table->foreignId('advisor_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->string('advisor_status', 30)->default('BELUM_DITENTUKAN')->after('advisor_id')->index();
        });

        // Create internship_advisor_assignments table
        Schema::create('internship_advisor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->constrained('internship_applications')->cascadeOnDelete();
            $table->foreignId('advisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            
            // Status: DIAJUKAN (Pending Dosen response), DITERIMA (Accepted), DITOLAK (Rejected)
            $table->string('status', 30)->default('DIAJUKAN')->index();
            
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->text('rejection_reason')->nullable(); // Mandatory if status is DITOLAK
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->index(['advisor_id', 'status'], 'idx_advisor_assign_adv_status');
            $table->index(['internship_application_id', 'status'], 'idx_advisor_assign_app_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_advisor_assignments');
        
        Schema::table('internship_applications', function (Blueprint $table) {
            $table->dropForeign(['advisor_id']);
            $table->dropColumn(['advisor_id', 'advisor_status']);
        });
    }
};
