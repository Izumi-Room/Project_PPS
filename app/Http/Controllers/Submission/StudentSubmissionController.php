<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\SubmitWorkRequest;
use App\Models\CourseConversion;
use App\Models\StudentSubmission;
use App\Models\SubmissionComponent;
use App\Models\SubmissionVersion;
use App\Services\Submission\DynamicSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentSubmissionController extends Controller
{
    protected DynamicSubmissionService $submissionService;

    public function __construct(DynamicSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Display a listing of submission components for the student's converted courses.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get student's course conversions that are approved or in progress
        $conversions = CourseConversion::where('user_id', $user->id)
            ->whereIn('status', [
                CourseConversion::STATUS_APPROVED_DOSEN_MK,
                CourseConversion::STATUS_VERIFIED_DOSBING,
                CourseConversion::STATUS_APPROVED_WADEK1,
                CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI,
            ])
            ->with(['course.submissionComponents' => fn ($q) => $q->active()])
            ->get();

        $selectedConversionId = $request->get('conversion_id', $conversions->first()?->id);
        $selectedConversion = $conversions->firstWhere('id', (int) $selectedConversionId);

        $components = collect();
        $submissions = collect();

        if ($selectedConversion) {
            $components = SubmissionComponent::where('course_id', $selectedConversion->course_id)
                ->active()
                ->orderBy('deadline', 'asc')
                ->get();

            $submissions = StudentSubmission::where('user_id', $user->id)
                ->where('course_conversion_id', $selectedConversion->id)
                ->with(['latestVersion', 'versions'])
                ->get()
                ->keyBy('submission_component_id');
        }

        return view('submissions.index', compact('conversions', 'selectedConversion', 'components', 'submissions'));
    }

    /**
     * Display specific component submission page with form and version history.
     */
    public function show(Request $request, SubmissionComponent $component): View
    {
        $user = $request->user();
        $conversionId = $request->get('conversion_id');

        $conversion = CourseConversion::where('user_id', $user->id)
            ->where('course_id', $component->course_id)
            ->when($conversionId, fn ($q) => $q->where('id', $conversionId))
            ->firstOrFail();

        $submission = StudentSubmission::where('user_id', $user->id)
            ->where('submission_component_id', $component->id)
            ->with(['versions.reviewer', 'latestVersion'])
            ->first();

        return view('submissions.show', compact('component', 'conversion', 'submission'));
    }

    /**
     * Store or resubmit student work for a component.
     */
    public function submit(SubmitWorkRequest $request, SubmissionComponent $component): RedirectResponse
    {
        $conversion = CourseConversion::findOrFail($request->course_conversion_id);

        $this->submissionService->submitWork(
            $request->user(),
            $component,
            $conversion,
            $request->validated(),
            $request->file('file')
        );

        return redirect()->route('submissions.show', [$component->id, 'conversion_id' => $conversion->id])
            ->with('success', "Tugas '{$component->name}' berhasil dikumpulkan.");
    }

    /**
     * Securely download version file.
     */
    public function downloadVersionFile(Request $request, SubmissionVersion $version): BinaryFileResponse
    {
        $user = $request->user();
        $submission = $version->submission;

        $isOwner = ($submission->user_id === $user->id);
        $isDosenMk = $user->hasRole('DOSEN_MK');
        $isDosbing = ($submission->courseConversion->internshipApplication->advisor_id === $user->id);
        $isPrivileged = $user->hasAnyRole(['KAPRODI', 'WADEK1', 'SUPERADMIN']);

        if (! $isOwner && ! $isDosenMk && ! $isDosbing && ! $isPrivileged) {
            abort(403, 'Akses unduh file submission ditolak.');
        }

        if (! $version->file_path || ! Storage::disk('public')->exists($version->file_path)) {
            abort(404, 'File lampiran versi ini tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($version->file_path);

        return response()->download($fullPath, $version->file_name);
    }
}
