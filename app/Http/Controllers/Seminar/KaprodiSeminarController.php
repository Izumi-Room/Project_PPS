<?php

namespace App\Http\Controllers\Seminar;

use App\Http\Controllers\Controller;
use App\Models\InternshipSeminar;
use App\Services\Seminar\SeminarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaprodiSeminarController extends Controller
{
    public function __construct(
        protected SeminarService $seminarService
    ) {}

    /**
     * Display listing of seminars waiting for Kaprodi acknowledgment.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = InternshipSeminar::with(['student', 'course', 'dosenMk', 'dosbing', 'internshipApplication'])
            ->where('is_required', true)
            ->whereNotNull('dosbing_acknowledged_at');

        if (! $user->hasRole('SUPERADMIN') && $user->study_program_id) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('study_program_id', $user->study_program_id);
            });
        }

        $seminars = $query->latest()->paginate(15);

        return view('kaprodi.seminars.index', compact('seminars'));
    }

    /**
     * Show seminar detail for Kaprodi.
     */
    public function show(InternshipSeminar $seminar): View
    {
        $seminar->loadMissing(['student', 'course', 'dosenMk', 'dosbing', 'kaprodi', 'wadek1']);

        return view('kaprodi.seminars.show', compact('seminar'));
    }

    /**
     * Acknowledge seminar by Kaprodi.
     */
    public function acknowledge(Request $request, InternshipSeminar $seminar): RedirectResponse
    {
        $notes = $request->input('notes');
        $this->seminarService->acknowledgeByKaprodi($seminar, $request->user(), $notes);

        return redirect()->back()->with('success', 'Jadwal seminar telah diketahui oleh Kaprodi dan diteruskan ke Wadek 1.');
    }
}
