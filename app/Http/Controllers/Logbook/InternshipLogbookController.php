<?php

namespace App\Http\Controllers\Logbook;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logbook\FeedbackLogbookRequest;
use App\Http\Requests\Logbook\StoreLogbookRequest;
use App\Http\Requests\Logbook\UpdateLogbookRequest;
use App\Models\InternshipApplication;
use App\Models\InternshipLogbook;
use App\Services\Logbook\LogbookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InternshipLogbookController extends Controller
{
    protected LogbookService $logbookService;

    public function __construct(LogbookService $logbookService)
    {
        $this->logbookService = $logbookService;
    }

    /**
     * Display a listing of logbooks for the authenticated student.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $applications = InternshipApplication::forUser($user->id)
            ->where('status', InternshipApplication::STATUS_APPROVED)
            ->with(['partnerInstitution', 'advisor'])
            ->get();

        $selectedAppId = $request->get('application_id', $applications->first()?->id);

        $logbooks = collect();
        $selectedApplication = null;

        if ($selectedAppId) {
            $selectedApplication = $applications->firstWhere('id', (int) $selectedAppId);
            if ($selectedApplication) {
                $logbooks = InternshipLogbook::forUser($user->id)
                    ->where('internship_application_id', $selectedApplication->id)
                    ->orderBy('week_number', 'asc')
                    ->orderBy('activity_date', 'asc')
                    ->paginate(15)
                    ->withQueryString();
            }
        }

        return view('logbooks.index', compact('applications', 'selectedApplication', 'logbooks'));
    }

    /**
     * Show form to create a new weekly logbook entry.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $applications = InternshipApplication::forUser($user->id)
            ->where('status', InternshipApplication::STATUS_APPROVED)
            ->with(['partnerInstitution', 'advisor'])
            ->get();

        if ($applications->isEmpty()) {
            return redirect()->route('logbooks.index')
                ->with('error', 'Anda belum memiliki pendaftaran magang yang telah disetujui resmi untuk mengisi logbook.');
        }

        $defaultAppId = $request->get('application_id', $applications->first()->id);

        // Next default week number
        $lastWeek = InternshipLogbook::where('internship_application_id', $defaultAppId)->max('week_number');
        $nextWeek = $lastWeek ? $lastWeek + 1 : 1;

        return view('logbooks.create', compact('applications', 'defaultAppId', 'nextWeek'));
    }

    /**
     * Store a newly created logbook entry.
     */
    public function store(StoreLogbookRequest $request): RedirectResponse
    {
        $logbook = $this->logbookService->createLogbook(
            $request->user(),
            $request->validated(),
            $request->file('attachment')
        );

        return redirect()->route('logbooks.show', $logbook->id)
            ->with('success', 'Catatan logbook mingguan berhasil disimpan.');
    }

    /**
     * Display a specific logbook entry.
     */
    public function show(Request $request, InternshipLogbook $logbook): View
    {
        $user = $request->user();

        $isOwner = $logbook->user_id === $user->id;
        $isDosbing = ($logbook->internshipApplication->advisor_id === $user->id);
        $isPrivileged = $user->hasAnyRole(['KAPRODI', 'WADEK1', 'SUPERADMIN']);

        if (! $isOwner && ! $isDosbing && ! $isPrivileged) {
            abort(403, 'Anda tidak berhak melihat logbook ini.');
        }

        $logbook->load(['internshipApplication.partnerInstitution', 'internshipApplication.advisor', 'student', 'reviewer']);

        return view('logbooks.show', compact('logbook', 'isOwner', 'isDosbing'));
    }

    /**
     * Show form to edit a logbook entry.
     */
    public function edit(Request $request, InternshipLogbook $logbook): View
    {
        if ($logbook->user_id !== $request->user()->id && ! $request->user()->hasRole('SUPERADMIN')) {
            abort(403, 'Anda tidak berhak mengubah logbook ini.');
        }

        $logbook->load('internshipApplication');

        return view('logbooks.edit', compact('logbook'));
    }

    /**
     * Update an existing logbook entry.
     */
    public function update(UpdateLogbookRequest $request, InternshipLogbook $logbook): RedirectResponse
    {
        $this->logbookService->updateLogbook(
            $logbook,
            $request->user(),
            $request->validated(),
            $request->file('attachment')
        );

        return redirect()->route('logbooks.show', $logbook->id)
            ->with('success', 'Catatan logbook berhasil diperbarui.');
    }

    /**
     * Remove the logbook entry.
     */
    public function destroy(Request $request, InternshipLogbook $logbook): RedirectResponse
    {
        $appId = $logbook->internship_application_id;
        $this->logbookService->deleteLogbook($logbook, $request->user());

        return redirect()->route('logbooks.index', ['application_id' => $appId])
            ->with('success', 'Catatan logbook berhasil dihapus.');
    }

    /**
     * Securely download logbook attachment file.
     */
    public function downloadAttachment(Request $request, InternshipLogbook $logbook): BinaryFileResponse
    {
        $user = $request->user();
        $isOwner = $logbook->user_id === $user->id;
        $isDosbing = ($logbook->internshipApplication->advisor_id === $user->id);
        $isPrivileged = $user->hasAnyRole(['KAPRODI', 'WADEK1', 'SUPERADMIN']);

        if (! $isOwner && ! $isDosbing && ! $isPrivileged) {
            abort(403, 'Akses lampiran logbook ditolak.');
        }

        if (! $logbook->attachment_path || ! Storage::disk('public')->exists($logbook->attachment_path)) {
            abort(404, 'File lampiran logbook tidak ditemukan.');
        }

        $filePath = Storage::disk('public')->path($logbook->attachment_path);

        return response()->download($filePath);
    }

    /**
     * Dosen Pembimbing views logbooks for a mentored student.
     */
    public function studentLogbooks(Request $request, InternshipApplication $internship): View
    {
        $user = $request->user();
        $isSuperadmin = $user->hasRole('SUPERADMIN');
        $isAdvisor = ($internship->advisor_id === $user->id);

        if (! $isSuperadmin && (! $user->hasRole('DOSBING') || ! $isAdvisor)) {
            abort(403, 'Anda tidak berhak melihat logbook mahasiswa bimbingan ini.');
        }

        $internship->load(['student', 'partnerInstitution', 'studyProgram', 'advisor']);

        $logbooks = InternshipLogbook::where('internship_application_id', $internship->id)
            ->orderBy('week_number', 'asc')
            ->orderBy('activity_date', 'asc')
            ->with('reviewer')
            ->paginate(15);

        return view('academic.logbooks.student', compact('internship', 'logbooks'));
    }

    /**
     * Dosen Pembimbing submits feedback on a student's logbook.
     */
    public function dosbingFeedback(FeedbackLogbookRequest $request, InternshipLogbook $logbook): RedirectResponse
    {
        $this->logbookService->provideFeedback(
            $logbook,
            $request->user(),
            $request->dosbing_feedback
        );

        return redirect()->back()->with('success', 'Catatan evaluasi logbook berhasil dikirimkan ke mahasiswa.');
    }
}
