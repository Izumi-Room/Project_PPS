<?php

namespace App\Http\Controllers\Conversion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseConversion\ResubmitCourseConversionRequest;
use App\Http\Requests\CourseConversion\StoreCourseConversionRequest;
use App\Models\Course;
use App\Models\CourseConversion;
use App\Models\InternshipApplication;
use App\Services\CourseConversion\CourseConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseConversionController extends Controller
{
    protected CourseConversionService $conversionService;

    public function __construct(CourseConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Display a listing of course conversions for the authenticated student.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = CourseConversion::with(['course', 'internshipApplication.partnerInstitution', 'internshipApplication.advisor'])
            ->forUser($user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->whereHas('course', function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('code', 'like', $term);
            });
        }

        $conversions = $query->paginate(10)->withQueryString();

        // Get approved internships with advisor for quick action
        $approvedInternships = InternshipApplication::forUser($user->id)
            ->where('status', InternshipApplication::STATUS_APPROVED)
            ->where('advisor_status', InternshipApplication::STATUS_ADVISOR_ACCEPTED)
            ->get();

        return view('conversions.index', compact('conversions', 'approvedInternships'));
    }

    /**
     * Show the form for creating a new course conversion request.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $applications = InternshipApplication::forUser($user->id)
            ->where('status', InternshipApplication::STATUS_APPROVED)
            ->where('advisor_status', InternshipApplication::STATUS_ADVISOR_ACCEPTED)
            ->with(['partnerInstitution', 'advisor', 'studyProgram'])
            ->get();

        if ($applications->isEmpty()) {
            return redirect()->route('conversions.index')->with('error', 'Anda belum memiliki pendaftaran magang yang telah disetujui resmi dan memiliki Dosen Pembimbing aktif.');
        }

        // Get available active courses
        $courses = Course::where('is_active', true)
            ->orderBy('semester', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('conversions.create', compact('applications', 'courses'));
    }

    /**
     * Store a newly created course conversion request in storage.
     */
    public function store(StoreCourseConversionRequest $request): RedirectResponse
    {
        $conversion = $this->conversionService->submitConversion($request->user(), $request->validated());

        return redirect()->route('conversions.show', $conversion->id)
            ->with('success', 'Pengajuan konversi mata kuliah berhasil dikirim dan menunggu persetujuan Dosen MK.');
    }

    /**
     * Display the specified course conversion details and status timeline.
     */
    public function show(Request $request, CourseConversion $conversion): View
    {
        $user = $request->user();

        // Authorization check
        if ($conversion->user_id !== $user->id && ! $user->hasAnyRole(['DOSEN_MK', 'DOSBING', 'WADEK1', 'KAPRODI', 'SUPERADMIN'])) {
            abort(403, 'Anda tidak berhak melihat rincian konversi mata kuliah ini.');
        }

        $conversion->load([
            'course',
            'internshipApplication.partnerInstitution',
            'internshipApplication.studyProgram',
            'internshipApplication.advisor',
            'dosenMk',
            'dosbing',
            'wadek1',
            'kaprodi',
            'statusHistories.actor',
        ]);

        return view('conversions.show', compact('conversion'));
    }

    /**
     * Resubmit a rejected course conversion request.
     */
    public function resubmit(ResubmitCourseConversionRequest $request, CourseConversion $conversion): RedirectResponse
    {
        $this->conversionService->resubmitConversion($conversion, $request->user(), $request->validated());

        return redirect()->route('conversions.show', $conversion->id)
            ->with('success', 'Pengajuan konversi mata kuliah berhasil diperbaiki dan diajukan ulang ke Dosen MK.');
    }
}
