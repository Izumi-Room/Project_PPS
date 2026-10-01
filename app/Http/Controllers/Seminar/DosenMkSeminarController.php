<?php

namespace App\Http\Controllers\Seminar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seminar\DecideSeminarRequest;
use App\Http\Requests\Seminar\RescheduleSeminarRequest;
use App\Models\CourseConversion;
use App\Models\InternshipSeminar;
use App\Services\Seminar\SeminarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosenMkSeminarController extends Controller
{
    public function __construct(
        protected SeminarService $seminarService
    ) {}

    /**
     * Display listing of seminars and conversions for Dosen MK.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Conversions where user is assigned dosen_mk or course instructor (or superadmin sees all)
        $conversionsQuery = CourseConversion::with(['student', 'course', 'internshipApplication.advisor', 'seminars'])
            ->whereIn('status', [
                CourseConversion::STATUS_APPROVED_DOSEN_MK,
                CourseConversion::STATUS_VERIFIED_DOSBING,
                CourseConversion::STATUS_APPROVED_WADEK1,
                CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI,
            ]);

        if (! $user->hasRole('SUPERADMIN')) {
            $conversionsQuery->where(function ($q) use ($user) {
                $q->where('dosen_mk_id', $user->id)
                    ->orWhereNull('dosen_mk_id');
            });
        }

        $conversions = $conversionsQuery->latest()->paginate(15);

        $seminarsQuery = InternshipSeminar::with(['student', 'course', 'internshipApplication.advisor']);
        if (! $user->hasRole('SUPERADMIN')) {
            $seminarsQuery->where('dosen_mk_id', $user->id);
        }
        $seminars = $seminarsQuery->latest()->get();

        return view('dosen-mk.seminars.index', compact('conversions', 'seminars'));
    }

    /**
     * Show form to decide or view seminar setup.
     */
    public function show(CourseConversion $conversion): View
    {
        $conversion->loadMissing(['student', 'course', 'internshipApplication.advisor', 'seminars']);
        $seminar = $conversion->seminars()->latest()->first();

        return view('dosen-mk.seminars.show', compact('conversion', 'seminar'));
    }

    /**
     * Store decision (YES / NO) and schedule if YES.
     */
    public function decide(DecideSeminarRequest $request): RedirectResponse
    {
        $conversion = CourseConversion::with('internshipApplication')->findOrFail($request->validated('course_conversion_id'));
        $isRequired = (bool) $request->validated('is_required');

        $scheduleData = [
            'scheduled_date' => $request->validated('scheduled_date'),
            'scheduled_time' => $request->validated('scheduled_time'),
            'location_or_link' => $request->validated('location_or_link'),
            'information' => $request->validated('information'),
        ];

        $seminar = $this->seminarService->decideSeminar($conversion, $request->user(), $isRequired, $scheduleData);

        $msg = $isRequired
            ? 'Jadwal seminar magang berhasil disimpan dan diajukan ke Dosen Pembimbing.'
            : 'Seminar magang dinyatakan TIDAK DIPERLUKAN untuk mata kuliah ini.';

        return redirect()->route('dosen-mk.seminars.index')->with('success', $msg);
    }

    /**
     * Reschedule an existing seminar.
     */
    public function reschedule(RescheduleSeminarRequest $request, InternshipSeminar $seminar): RedirectResponse
    {
        $this->seminarService->rescheduleSeminar($seminar, $request->user(), $request->validated());

        return redirect()->back()->with('success', 'Jadwal seminar berhasil diperbarui dan notifikasi telah dikirimkan ke pihak terkait.');
    }

    /**
     * Mark seminar as conducted after Wadek 1 approval.
     */
    public function conduct(Request $request, InternshipSeminar $seminar): RedirectResponse
    {
        $this->seminarService->conductSeminar($seminar, $request->user());

        return redirect()->back()->with('success', 'Seminar magang telah berhasil ditandai sebagai DILAKSANAKAN.');
    }
}
