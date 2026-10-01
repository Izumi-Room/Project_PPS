<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Phase 1B: Authentication & RBAC
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Standalone Error Pages for demonstration / testing
Route::get('/unauthorized', fn () => response()->view('errors.401', [], 401))->name('unauthorized');
Route::get('/forbidden', fn () => response()->view('errors.403', [], 403))->name('forbidden');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // General user profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Backend service test route (requires auth, asserts role at service layer)
    Route::get('/test-service-action', [DashboardController::class, 'testServiceAction'])->name('test.service.action');

    // Protected resources requiring at least one active assigned role
    Route::middleware('has.role')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('home');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Superadmin & Kaprodi guarded route
        Route::middleware('role:SUPERADMIN')->group(function () {
            Route::get('/admin/superadmin-panel', [DashboardController::class, 'superadmin'])->name('admin.superadmin');

            // Phase 1C: Master Data Management Routes
            Route::prefix('master')->name('master.')->group(function () {
                // 1. Prodi
                Route::post('study-programs/{study_program}/toggle', [\App\Http\Controllers\Master\StudyProgramController::class, 'toggleStatus'])->name('study-programs.toggle');
                Route::resource('study-programs', \App\Http\Controllers\Master\StudyProgramController::class);

                // 2. Instansi Mitra
                Route::post('partner-institutions/{partner_institution}/toggle', [\App\Http\Controllers\Master\PartnerInstitutionController::class, 'toggleStatus'])->name('partner-institutions.toggle');
                Route::resource('partner-institutions', \App\Http\Controllers\Master\PartnerInstitutionController::class);

                // 3. Mata Kuliah
                Route::post('courses/{course}/toggle', [\App\Http\Controllers\Master\CourseController::class, 'toggleStatus'])->name('courses.toggle');
                Route::resource('courses', \App\Http\Controllers\Master\CourseController::class);

                // 4. Periode Magang
                Route::post('internship-periods/{internship_period}/toggle', [\App\Http\Controllers\Master\InternshipPeriodController::class, 'toggleStatus'])->name('internship-periods.toggle');
                Route::resource('internship-periods', \App\Http\Controllers\Master\InternshipPeriodController::class);

                // 5. Pengguna (User)
                Route::post('users/{user}/toggle', [\App\Http\Controllers\Master\UserController::class, 'toggleStatus'])->name('users.toggle');
                Route::resource('users', \App\Http\Controllers\Master\UserController::class);

                // 6. Role
                Route::resource('roles', \App\Http\Controllers\Master\RoleController::class);

                // 7. User Role Assignments
                Route::get('user-roles', [\App\Http\Controllers\Master\UserRoleController::class, 'index'])->name('user-roles.index');
                Route::get('user-roles/{user}/edit', [\App\Http\Controllers\Master\UserRoleController::class, 'edit'])->name('user-roles.edit');
                Route::put('user-roles/{user}', [\App\Http\Controllers\Master\UserRoleController::class, 'update'])->name('user-roles.update');
                Route::delete('user-roles/{user}/roles/{role}', [\App\Http\Controllers\Master\UserRoleController::class, 'detach'])->name('user-roles.detach');

                // 8. Audit Logs (Phase 1D: Role & User security changes trail)
                Route::get('audit-logs', [\App\Http\Controllers\Master\AuditLogController::class, 'index'])->name('audit-logs.index');
            });
        });

        // Read-only catalog endpoints for authenticated users (MHS, TU, Dosen, etc.)
        Route::prefix('master-catalog')->name('master.catalog.')->group(function () {
            Route::get('periods/active', [\App\Http\Controllers\Master\InternshipPeriodController::class, 'getActivePeriod'])->name('periods.active');
            Route::get('study-programs', [\App\Http\Controllers\Master\StudyProgramController::class, 'index'])->name('study-programs.list');
            Route::get('partner-institutions', [\App\Http\Controllers\Master\PartnerInstitutionController::class, 'index'])->name('partner-institutions.list');
            Route::get('courses', [\App\Http\Controllers\Master\CourseController::class, 'index'])->name('courses.list');
        });

        // Academic portal guarded route (Accessible to DOSBING and/or DOSEN_MK)
        Route::middleware('role:DOSBING,DOSEN_MK')->group(function () {
            Route::get('/academic/portal', [DashboardController::class, 'academic'])->name('academic.portal');
        });

        // ==========================================
        // PHASE 2: PENDAFTARAN MAGANG & WORKFLOW
        // ==========================================

        // In-App Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Notification\NotificationController::class, 'index'])->name('index');
            Route::post('{notification}/read', [\App\Http\Controllers\Notification\NotificationController::class, 'markAsRead'])->name('read');
            Route::post('read-all', [\App\Http\Controllers\Notification\NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::get('unread-count', [\App\Http\Controllers\Notification\NotificationController::class, 'unreadCount'])->name('unread-count');
        });

        // 1. Mahasiswa & General Applications
        Route::resource('internships', \App\Http\Controllers\Internship\InternshipApplicationController::class);
        Route::post('internships/{internship}/acceptance', [\App\Http\Controllers\Internship\InternshipApplicationController::class, 'uploadAcceptance'])->name('internships.acceptance.upload');
        Route::get('internships/{internship}/documents/{document}/download', [\App\Http\Controllers\Internship\InternshipApplicationController::class, 'downloadDocument'])->name('internships.documents.download');
        Route::get('internships/{internship}/reference-letter/download', [\App\Http\Controllers\Internship\InternshipApplicationController::class, 'downloadReferenceLetter'])->name('internships.reference-letter.download');
        Route::get('internships/{internship}/acceptance-letter/download', [\App\Http\Controllers\Internship\InternshipApplicationController::class, 'downloadAcceptanceLetter'])->name('internships.acceptance-letter.download');

        // 2. Tata Usaha (TU) Verification Queue & Reference Letter Issuance
        Route::middleware('role:TU,SUPERADMIN')->prefix('tu')->name('tu.')->group(function () {
            Route::get('internships', [\App\Http\Controllers\Internship\TuVerificationController::class, 'index'])->name('internships.index');
            Route::get('internships/{internship}', [\App\Http\Controllers\Internship\TuVerificationController::class, 'show'])->name('internships.show');
            Route::post('internships/{internship}/review', [\App\Http\Controllers\Internship\TuVerificationController::class, 'review'])->name('internships.review');
            Route::get('internships/{internship}/print-letter', [\App\Http\Controllers\Internship\TuVerificationController::class, 'printLetter'])->name('internships.print-letter');
        });

        // 3. Kaprodi Verification Queue & Advisor Assignment
        Route::middleware('role:KAPRODI,SUPERADMIN')->prefix('kaprodi')->name('kaprodi.')->group(function () {
            Route::get('internships', [\App\Http\Controllers\Internship\KaprodiVerificationController::class, 'index'])->name('internships.index');
            Route::get('internships/{internship}', [\App\Http\Controllers\Internship\KaprodiVerificationController::class, 'show'])->name('internships.show');
            Route::post('internships/{internship}/review', [\App\Http\Controllers\Internship\KaprodiVerificationController::class, 'review'])->name('internships.review');

            // Phase 3: Penentuan Dosen Pembimbing (Kaprodi Queue)
            Route::get('advisors', [\App\Http\Controllers\Internship\KaprodiAdvisorAssignmentController::class, 'index'])->name('advisors.index');
            Route::get('advisors/{internship}', [\App\Http\Controllers\Internship\KaprodiAdvisorAssignmentController::class, 'show'])->name('advisors.show');
            Route::post('advisors/{internship}/assign', [\App\Http\Controllers\Internship\KaprodiAdvisorAssignmentController::class, 'assign'])->name('advisors.assign');

            // Phase 4: Kaprodi Konversi MK Pengesahan ("Diketahui")
            Route::get('conversions', [\App\Http\Controllers\Conversion\KaprodiConversionController::class, 'index'])->name('conversions.index');
            Route::get('conversions/{conversion}', [\App\Http\Controllers\Conversion\KaprodiConversionController::class, 'show'])->name('conversions.show');
            Route::post('conversions/{conversion}/acknowledge', [\App\Http\Controllers\Conversion\KaprodiConversionController::class, 'acknowledge'])->name('conversions.acknowledge');

            // Phase 6: Kaprodi Seminar Magang ("Diketahui")
            Route::get('seminars', [\App\Http\Controllers\Seminar\KaprodiSeminarController::class, 'index'])->name('seminars.index');
            Route::get('seminars/{seminar}', [\App\Http\Controllers\Seminar\KaprodiSeminarController::class, 'show'])->name('seminars.show');
            Route::post('seminars/{seminar}/acknowledge', [\App\Http\Controllers\Seminar\KaprodiSeminarController::class, 'acknowledge'])->name('seminars.acknowledge');
        });

        // 4. Wakil Dekan 1 (Wadek 1) Approval Queue
        Route::middleware('role:WADEK1,SUPERADMIN')->prefix('wadek1')->name('wadek1.')->group(function () {
            Route::get('internships', [\App\Http\Controllers\Internship\Wadek1ApprovalController::class, 'index'])->name('internships.index');
            Route::get('internships/{internship}', [\App\Http\Controllers\Internship\Wadek1ApprovalController::class, 'show'])->name('internships.show');
            Route::post('internships/{internship}/review', [\App\Http\Controllers\Internship\Wadek1ApprovalController::class, 'review'])->name('internships.review');

            // Phase 4: Wadek 1 Konversi MK Approval
            Route::get('conversions', [\App\Http\Controllers\Conversion\Wadek1ConversionController::class, 'index'])->name('conversions.index');
            Route::get('conversions/{conversion}', [\App\Http\Controllers\Conversion\Wadek1ConversionController::class, 'show'])->name('conversions.show');
            Route::post('conversions/{conversion}/review', [\App\Http\Controllers\Conversion\Wadek1ConversionController::class, 'review'])->name('conversions.review');

            // Phase 6: Wadek 1 Seminar Magang Approval & Rejection
            Route::get('seminars', [\App\Http\Controllers\Seminar\Wadek1SeminarController::class, 'index'])->name('seminars.index');
            Route::get('seminars/{seminar}', [\App\Http\Controllers\Seminar\Wadek1SeminarController::class, 'show'])->name('seminars.show');
            Route::post('seminars/{seminar}/approve', [\App\Http\Controllers\Seminar\Wadek1SeminarController::class, 'approve'])->name('seminars.approve');
            Route::post('seminars/{seminar}/reject', [\App\Http\Controllers\Seminar\Wadek1SeminarController::class, 'reject'])->name('seminars.reject');
        });

        // 5. Phase 3 & 4: Dosen Pembimbing (DOSBING) Academic Portal
        Route::middleware('role:DOSBING,SUPERADMIN')->prefix('academic')->name('academic.')->group(function () {
            Route::get('advisor-assignments', [\App\Http\Controllers\Internship\DosenAdvisorAssignmentController::class, 'index'])->name('advisor-assignments.index');
            Route::post('advisor-assignments/{assignment}/respond', [\App\Http\Controllers\Internship\DosenAdvisorAssignmentController::class, 'respond'])->name('advisor-assignments.respond');

            // Phase 4: Dosbing Konversi MK Verification
            Route::get('conversions', [\App\Http\Controllers\Conversion\DosbingConversionController::class, 'index'])->name('conversions.index');
            Route::get('conversions/{conversion}', [\App\Http\Controllers\Conversion\DosbingConversionController::class, 'show'])->name('conversions.show');
            Route::post('conversions/{conversion}/review', [\App\Http\Controllers\Conversion\DosbingConversionController::class, 'review'])->name('conversions.review');

            // Phase 4: Dosbing Student Logbook Reviews
            Route::get('internships/{internship}/logbooks', [\App\Http\Controllers\Logbook\InternshipLogbookController::class, 'studentLogbooks'])->name('logbooks.student');
            Route::post('logbooks/{logbook}/feedback', [\App\Http\Controllers\Logbook\InternshipLogbookController::class, 'dosbingFeedback'])->name('logbooks.feedback');

            // Phase 6: Dosbing Seminar Magang ("Mengetahui")
            Route::get('seminars', [\App\Http\Controllers\Seminar\DosbingSeminarController::class, 'index'])->name('seminars.index');
            Route::get('seminars/{seminar}', [\App\Http\Controllers\Seminar\DosbingSeminarController::class, 'show'])->name('seminars.show');
            Route::post('seminars/{seminar}/acknowledge', [\App\Http\Controllers\Seminar\DosbingSeminarController::class, 'acknowledge'])->name('seminars.acknowledge');
        });

        // ==========================================
        // PHASE 4: DOSEN MK, KONVERSI & LOGBOOK
        // ==========================================

        // 6. Dosen Pengampu Mata Kuliah (DOSEN_MK) Queue & Approval
        Route::middleware('role:DOSEN_MK,SUPERADMIN')->prefix('dosen-mk')->name('dosen-mk.')->group(function () {
            Route::get('conversions', [\App\Http\Controllers\Conversion\DosenMkConversionController::class, 'index'])->name('conversions.index');
            Route::get('conversions/{conversion}', [\App\Http\Controllers\Conversion\DosenMkConversionController::class, 'show'])->name('conversions.show');
            Route::post('conversions/{conversion}/review', [\App\Http\Controllers\Conversion\DosenMkConversionController::class, 'review'])->name('conversions.review');

            // Phase 5: Dynamic Submission Components & Reviews
            Route::resource('components', \App\Http\Controllers\Submission\DosenMkComponentController::class);
            Route::get('submissions', [\App\Http\Controllers\Submission\DosenMkSubmissionReviewController::class, 'index'])->name('submissions.index');
            Route::get('submissions/{submission}', [\App\Http\Controllers\Submission\DosenMkSubmissionReviewController::class, 'show'])->name('submissions.show');
            Route::post('submissions/{submission}/review', [\App\Http\Controllers\Submission\DosenMkSubmissionReviewController::class, 'review'])->name('submissions.review');
            Route::get('submissions/versions/{version}/download', [\App\Http\Controllers\Submission\DosenMkSubmissionReviewController::class, 'downloadVersionFile'])->name('submissions.version.download');

            // Phase 6: Dosen MK Seminar Scheduling & Execution
            Route::get('seminars', [\App\Http\Controllers\Seminar\DosenMkSeminarController::class, 'index'])->name('seminars.index');
            Route::get('seminars/conversions/{conversion}', [\App\Http\Controllers\Seminar\DosenMkSeminarController::class, 'show'])->name('seminars.show');
            Route::post('seminars/decide', [\App\Http\Controllers\Seminar\DosenMkSeminarController::class, 'decide'])->name('seminars.decide');
            Route::post('seminars/{seminar}/reschedule', [\App\Http\Controllers\Seminar\DosenMkSeminarController::class, 'reschedule'])->name('seminars.reschedule');
            Route::post('seminars/{seminar}/conduct', [\App\Http\Controllers\Seminar\DosenMkSeminarController::class, 'conduct'])->name('seminars.conduct');
        });

        // 7. Mahasiswa Course Conversions (Konversi MK)
        Route::resource('conversions', \App\Http\Controllers\Conversion\CourseConversionController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('conversions/{conversion}/resubmit', [\App\Http\Controllers\Conversion\CourseConversionController::class, 'resubmit'])->name('conversions.resubmit');

        // 8. Mahasiswa Internship Logbooks (Logbook Mingguan)
        Route::get('logbooks/{logbook}/attachment', [\App\Http\Controllers\Logbook\InternshipLogbookController::class, 'downloadAttachment'])->name('logbooks.attachment.download');
        Route::resource('logbooks', \App\Http\Controllers\Logbook\InternshipLogbookController::class);

        // ==========================================
        // PHASE 5: DYNAMIC SUBMISSION (MAHASISWA)
        // ==========================================
        Route::get('submissions', [\App\Http\Controllers\Submission\StudentSubmissionController::class, 'index'])->name('submissions.index');
        Route::get('submissions/components/{component}', [\App\Http\Controllers\Submission\StudentSubmissionController::class, 'show'])->name('submissions.show');
        Route::post('submissions/components/{component}/submit', [\App\Http\Controllers\Submission\StudentSubmissionController::class, 'submit'])->name('submissions.submit');
        Route::get('submissions/versions/{version}/download', [\App\Http\Controllers\Submission\StudentSubmissionController::class, 'downloadVersionFile'])->name('submissions.download');

        // ==========================================
        // PHASE 6: SEMINAR MAGANG (MAHASISWA)
        // ==========================================
        Route::get('seminars', [\App\Http\Controllers\Seminar\StudentSeminarController::class, 'index'])->name('seminars.index');
        Route::get('seminars/{seminar}', [\App\Http\Controllers\Seminar\StudentSeminarController::class, 'show'])->name('seminars.show');
    });
});



