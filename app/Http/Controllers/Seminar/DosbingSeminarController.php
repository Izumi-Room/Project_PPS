<?php

namespace App\Http\Controllers\Seminar;

use App\Http\Controllers\Controller;
use App\Models\InternshipSeminar;
use App\Services\Seminar\SeminarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosbingSeminarController extends Controller
{
    public function __construct(
        protected SeminarService $seminarService
    ) {}

    /**
     * Display listing of seminars waiting for Dosbing acknowledgment.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = InternshipSeminar::with(['student', 'course', 'dosenMk', 'internshipApplication'])
            ->where('is_required', true);

        if (! $user->hasRole('SUPERADMIN')) {
            $query->whereHas('internshipApplication', function ($q) use ($user) {
                $q->where('advisor_id', $user->id);
            });
        }

        $seminars = $query->latest()->paginate(15);

        return view('academic.seminars.index', compact('seminars'));
    }

    /**
     * Show seminar detail for Dosbing.
     */
    public function show(InternshipSeminar $seminar): View
    {
        $seminar->loadMissing(['student', 'course', 'dosenMk', 'internshipApplication.advisor', 'kaprodi', 'wadek1']);

        return view('academic.seminars.show', compact('seminar'));
    }

    /**
     * Acknowledge seminar by Dosbing.
     */
    public function acknowledge(Request $request, InternshipSeminar $seminar): RedirectResponse
    {
        $notes = $request->input('notes');
        $this->seminarService->acknowledgeByDosbing($seminar, $request->user(), $notes);

        return redirect()->back()->with('success', 'Anda telah mengonfirmasi mengetahui jadwal seminar magang ini.');
    }
}
