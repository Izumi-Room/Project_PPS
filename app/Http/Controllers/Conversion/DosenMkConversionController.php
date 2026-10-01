<?php

namespace App\Http\Controllers\Conversion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseConversion\ReviewConversionRequest;
use App\Models\CourseConversion;
use App\Services\CourseConversion\CourseConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosenMkConversionController extends Controller
{
    protected CourseConversionService $conversionService;

    public function __construct(CourseConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Display conversion queue for Dosen Pengampu Mata Kuliah.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'queue'); // 'queue' or 'history'

        $query = CourseConversion::with(['course', 'student', 'internshipApplication.partnerInstitution']);

        if ($tab === 'queue') {
            $query->where('status', CourseConversion::STATUS_SUBMITTED);
        } else {
            $query->where('status', '!=', CourseConversion::STATUS_SUBMITTED);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('student', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('identity_number', 'like', $term))
                  ->orWhereHas('course', fn ($cq) => $cq->where('name', 'like', $term)->orWhere('code', 'like', $term));
            });
        }

        $conversions = $query->latest()->paginate(10)->withQueryString();
        $pendingCount = CourseConversion::where('status', CourseConversion::STATUS_SUBMITTED)->count();

        return view('dosen-mk.conversions.index', compact('conversions', 'tab', 'pendingCount'));
    }

    /**
     * Show conversion details for Dosen MK review.
     */
    public function show(CourseConversion $conversion): View
    {
        $conversion->load([
            'course',
            'student',
            'internshipApplication.partnerInstitution',
            'internshipApplication.advisor',
            'statusHistories.actor',
        ]);

        return view('dosen-mk.conversions.show', compact('conversion'));
    }

    /**
     * Review the conversion (Approve / Reject with reason).
     */
    public function review(ReviewConversionRequest $request, CourseConversion $conversion): RedirectResponse
    {
        $this->conversionService->reviewByDosenMk(
            $conversion,
            $request->user(),
            $request->action,
            $request->notes,
            $request->rejection_reason
        );

        $actionWord = $request->action === 'APPROVE' ? 'disetujui' : 'ditolak';

        return redirect()->route('dosen-mk.conversions.index')
            ->with('success', "Pengajuan konversi mata kuliah berhasil {$actionWord}.");
    }
}
