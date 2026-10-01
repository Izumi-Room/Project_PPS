<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\ReviewSubmissionRequest;
use App\Models\Course;
use App\Models\StudentSubmission;
use App\Models\SubmissionComponent;
use App\Models\SubmissionVersion;
use App\Services\Submission\DynamicSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DosenMkSubmissionReviewController extends Controller
{
    protected DynamicSubmissionService $submissionService;

    public function __construct(DynamicSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Display a listing of submissions for review.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'queue'); // queue or history

        $query = StudentSubmission::with([
            'component.course',
            'student',
            'courseConversion.internshipApplication.partnerInstitution',
            'latestVersion',
        ]);

        if ($tab === 'queue') {
            $query->whereIn('status', [StudentSubmission::STATUS_SUBMITTED, StudentSubmission::STATUS_RESUBMITTED]);
        } else {
            $query->whereIn('status', [StudentSubmission::STATUS_APPROVED, StudentSubmission::STATUS_REVISION_NEEDED]);
        }

        if ($request->filled('course_id')) {
            $query->whereHas('component', fn ($cq) => $cq->where('course_id', $request->course_id));
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('student', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('identifier_number', 'like', $term))
                  ->orWhereHas('component', fn ($cq) => $cq->where('name', 'like', $term));
            });
        }

        $submissions = $query->latest('latest_submitted_at')->paginate(15)->withQueryString();
        $pendingCount = StudentSubmission::whereIn('status', [StudentSubmission::STATUS_SUBMITTED, StudentSubmission::STATUS_RESUBMITTED])->count();
        $courses = Course::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('dosen-mk.submissions.index', compact('submissions', 'tab', 'pendingCount', 'courses'));
    }

    /**
     * Show detail of student submission and version history.
     */
    public function show(StudentSubmission $submission): View
    {
        $submission->load([
            'component.course',
            'student',
            'courseConversion.internshipApplication.partnerInstitution',
            'courseConversion.internshipApplication.advisor',
            'versions.reviewer',
        ]);

        return view('dosen-mk.submissions.show', compact('submission'));
    }

    /**
     * Review the submission (Approve or Request Revision with mandatory feedback).
     */
    public function review(ReviewSubmissionRequest $request, StudentSubmission $submission): RedirectResponse
    {
        $this->submissionService->reviewSubmission(
            $submission,
            $request->user(),
            $request->action,
            $request->feedback
        );

        $actionWord = $request->action === 'APPROVE' ? 'disetujui' : 'diminta revisi perbaikan';

        return redirect()->route('dosen-mk.submissions.show', $submission->id)
            ->with('success', "Pengumpulan tugas mahasiswa berhasil {$actionWord}.");
    }

    /**
     * Securely download version file.
     */
    public function downloadVersionFile(SubmissionVersion $version): BinaryFileResponse
    {
        if (! $version->file_path || ! Storage::disk('public')->exists($version->file_path)) {
            abort(404, 'File lampiran versi ini tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($version->file_path);

        return response()->download($fullPath, $version->file_name);
    }
}
