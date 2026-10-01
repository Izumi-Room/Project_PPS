<?php

namespace App\Http\Controllers\Conversion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseConversion\ReviewConversionRequest;
use App\Models\CourseConversion;
use App\Services\CourseConversion\CourseConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Wadek1ConversionController extends Controller
{
    protected CourseConversionService $conversionService;

    public function __construct(CourseConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Display conversion approval queue for Wakil Dekan 1.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'queue');

        $query = CourseConversion::with(['course', 'student', 'internshipApplication.studyProgram', 'internshipApplication.partnerInstitution']);

        if ($tab === 'queue') {
            $query->where('status', CourseConversion::STATUS_VERIFIED_DOSBING);
        } else {
            $query->where('status', '!=', CourseConversion::STATUS_VERIFIED_DOSBING);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('student', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('identity_number', 'like', $term))
                  ->orWhereHas('course', fn ($cq) => $cq->where('name', 'like', $term)->orWhere('code', 'like', $term));
            });
        }

        $conversions = $query->latest()->paginate(10)->withQueryString();
        $pendingCount = CourseConversion::where('status', CourseConversion::STATUS_VERIFIED_DOSBING)->count();

        return view('wadek1.conversions.index', compact('conversions', 'tab', 'pendingCount'));
    }

    /**
     * Show conversion details for Wadek 1 approval.
     */
    public function show(CourseConversion $conversion): View
    {
        $conversion->load([
            'course',
            'student',
            'internshipApplication.studyProgram',
            'internshipApplication.partnerInstitution',
            'internshipApplication.advisor',
            'dosenMk',
            'dosbing',
            'statusHistories.actor',
        ]);

        return view('wadek1.conversions.show', compact('conversion'));
    }

    /**
     * Approve or reject the conversion request.
     */
    public function review(ReviewConversionRequest $request, CourseConversion $conversion): RedirectResponse
    {
        $this->conversionService->approveByWadek1(
            $conversion,
            $request->user(),
            $request->action,
            $request->notes,
            $request->rejection_reason
        );

        $actionWord = $request->action === 'APPROVE' ? 'disetujui' : 'ditolak';

        return redirect()->route('wadek1.conversions.index')
            ->with('success', "Pengajuan konversi mata kuliah berhasil {$actionWord} oleh Wakil Dekan 1.");
    }
}
