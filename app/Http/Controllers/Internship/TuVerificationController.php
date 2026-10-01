<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internship\TuReviewRequest;
use App\Models\InternshipApplication;
use App\Models\StudyProgram;
use App\Services\Internship\InternshipWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TuVerificationController extends Controller
{
    protected InternshipWorkflowService $workflowService;

    public function __construct(InternshipWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display TU Queue of pending applications.
     */
    public function index(Request $request): View
    {
        $query = InternshipApplication::with(['student', 'partnerInstitution', 'internshipPeriod', 'studyProgram'])
            ->latest();

        $activeTab = $request->query('tab', 'queue');

        if ($activeTab === 'queue') {
            $query->queueForTu();
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
        $pendingCount = InternshipApplication::queueForTu()->count();

        return view('tu.internships.index', compact('applications', 'studyPrograms', 'activeTab', 'pendingCount'));
    }

    /**
     * Display detail of application for TU with verification controls and document viewer.
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

        return view('tu.internships.show', compact('internship'));
    }

    /**
     * Process TU review decision (Pass / Return for correction).
     */
    public function review(TuReviewRequest $request, InternshipApplication $internship): RedirectResponse
    {
        $decision = $request->input('decision');
        $reason = $request->input('reason');
        $refNumber = $request->input('reference_letter_number');
        $refFile = $request->file('reference_letter_file');

        $this->workflowService->reviewByTu(
            $internship,
            Auth::user(),
            $decision,
            $reason,
            $refNumber,
            $refFile
        );

        $msg = $decision === 'PASS'
            ? 'Aplikasi magang berhasil divalidasi dan diteruskan ke Kaprodi.'
            : 'Aplikasi magang dikembalikan ke mahasiswa untuk perbaikan dokumen.';

        return redirect()->route('tu.internships.index')->with('success', $msg);
    }

    /**
     * Print / Preview official Surat Pengantar format.
     */
    public function printLetter(InternshipApplication $internship): View
    {
        $internship->load(['student', 'studyProgram', 'partnerInstitution', 'internshipPeriod']);

        return view('internships.reference-letter', compact('internship'));
    }
}
