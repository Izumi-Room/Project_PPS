<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internship\Wadek1ReviewRequest;
use App\Models\InternshipApplication;
use App\Models\StudyProgram;
use App\Services\Internship\InternshipWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class Wadek1ApprovalController extends Controller
{
    protected InternshipWorkflowService $workflowService;

    public function __construct(InternshipWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display Wadek 1 Queue of applications awaiting final approval.
     */
    public function index(Request $request): View
    {
        $query = InternshipApplication::with(['student', 'partnerInstitution', 'internshipPeriod', 'studyProgram'])
            ->latest();

        $activeTab = $request->query('tab', 'queue');

        if ($activeTab === 'queue') {
            $query->queueForWadek1();
        } else {
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
        $pendingCount = InternshipApplication::queueForWadek1()->count();

        return view('wadek1.internships.index', compact('applications', 'studyPrograms', 'activeTab', 'pendingCount'));
    }

    /**
     * Display application detail for Wadek 1 approval.
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

        return view('wadek1.internships.show', compact('internship'));
    }

    /**
     * Process Wadek 1 decision (Approve / Reject).
     */
    public function review(Wadek1ReviewRequest $request, InternshipApplication $internship): RedirectResponse
    {
        $decision = $request->input('decision');
        $reason = $request->input('reason');

        $this->workflowService->reviewByWadek1(
            $internship,
            Auth::user(),
            $decision,
            $reason
        );

        $msg = $decision === 'APPROVE'
            ? 'Pendaftaran magang berhasil disetujui penuh oleh Wakil Dekan 1.'
            : 'Pendaftaran magang telah ditolak oleh Wakil Dekan 1.';

        return redirect()->route('wadek1.internships.index')->with('success', $msg);
    }
}
