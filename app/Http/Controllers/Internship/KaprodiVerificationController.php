<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internship\KaprodiReviewRequest;
use App\Models\InternshipApplication;
use App\Models\StudyProgram;
use App\Services\Internship\InternshipWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KaprodiVerificationController extends Controller
{
    protected InternshipWorkflowService $workflowService;

    public function __construct(InternshipWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display Kaprodi Queue of applications passed by TU.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = InternshipApplication::with(['student', 'partnerInstitution', 'internshipPeriod', 'studyProgram'])
            ->latest();

        $activeTab = $request->query('tab', 'queue');

        if ($activeTab === 'queue') {
            $query->queueForKaprodi($user->study_program_id);
        } else {
            // All non-draft applications
            if ($user->study_program_id && ! $user->hasRole('SUPERADMIN')) {
                $query->where('study_program_id', $user->study_program_id);
            }
            $query->where('status', '!=', InternshipApplication::STATUS_DRAFT);
        }

        if ($request->filled('study_program_id')) {
            $query->filterProdi($request->study_program_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $applications = $query->paginate(10)->withQueryString();
        $studyPrograms = StudyProgram::active()->get();
        $pendingCount = InternshipApplication::queueForKaprodi($user->study_program_id)->count();

        return view('kaprodi.internships.index', compact('applications', 'studyPrograms', 'activeTab', 'pendingCount'));
    }

    /**
     * Display application detail for Kaprodi verification.
     */
    public function show(InternshipApplication $internship): View
    {
        $internship->load([
            'student.studyProgram',
            'studyProgram',
            'partnerInstitution',
            'internshipPeriod',
            'documents',
            'statusHistories.actor',
        ]);

        return view('kaprodi.internships.show', compact('internship'));
    }

    /**
     * Process Kaprodi decision (Verify / Reject).
     */
    public function review(KaprodiReviewRequest $request, InternshipApplication $internship): RedirectResponse
    {
        $decision = $request->input('decision');
        $reason = $request->input('reason');

        $this->workflowService->reviewByKaprodi(
            $internship,
            Auth::user(),
            $decision,
            $reason
        );

        $msg = $decision === 'VERIFY'
            ? 'Pendaftaran magang berhasil diverifikasi oleh Kaprodi dan diteruskan ke Wadek 1.'
            : 'Pendaftaran magang telah ditolak oleh Kaprodi.';

        return redirect()->route('kaprodi.internships.index')->with('success', $msg);
    }
}
