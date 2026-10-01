<?php

namespace App\Http\Controllers\Seminar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seminar\RejectSeminarRequest;
use App\Models\InternshipSeminar;
use App\Services\Seminar\SeminarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Wadek1SeminarController extends Controller
{
    public function __construct(
        protected SeminarService $seminarService
    ) {}

    /**
     * Display listing of seminars waiting for Wadek 1 approval.
     */
    public function index(Request $request): View
    {
        $seminars = InternshipSeminar::with(['student', 'course', 'dosenMk', 'dosbing', 'kaprodi'])
            ->where('is_required', true)
            ->where('status', InternshipSeminar::STATUS_ACKNOWLEDGED)
            ->latest()
            ->paginate(15);

        $history = InternshipSeminar::with(['student', 'course', 'dosenMk', 'dosbing', 'kaprodi'])
            ->whereIn('status', [InternshipSeminar::STATUS_APPROVED, InternshipSeminar::STATUS_CONDUCTED, InternshipSeminar::STATUS_REJECTED])
            ->latest()
            ->paginate(10, ['*'], 'history_page');

        return view('wadek1.seminars.index', compact('seminars', 'history'));
    }

    /**
     * Show seminar detail for Wadek 1.
     */
    public function show(InternshipSeminar $seminar): View
    {
        $seminar->loadMissing(['student', 'course', 'dosenMk', 'dosbing', 'kaprodi', 'wadek1']);

        return view('wadek1.seminars.show', compact('seminar'));
    }

    /**
     * Approve seminar by Wadek 1.
     */
    public function approve(Request $request, InternshipSeminar $seminar): RedirectResponse
    {
        $this->seminarService->approveByWadek1($seminar, $request->user());

        return redirect()->route('wadek1.seminars.index')->with('success', 'Seminar magang telah berhasil disetujui.');
    }

    /**
     * Reject seminar by Wadek 1 with mandatory reason.
     */
    public function reject(RejectSeminarRequest $request, InternshipSeminar $seminar): RedirectResponse
    {
        $reason = $request->validated('rejection_reason');
        $this->seminarService->rejectByWadek1($seminar, $request->user(), $reason);

        return redirect()->route('wadek1.seminars.index')->with('success', 'Seminar magang telah ditolak dan catatan telah dikirimkan ke pihak terkait.');
    }
}
