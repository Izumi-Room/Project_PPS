<?php

namespace App\Http\Controllers\Conversion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseConversion\ReviewConversionRequest;
use App\Models\CourseConversion;
use App\Services\CourseConversion\CourseConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosbingConversionController extends Controller
{
    protected CourseConversionService $conversionService;

    public function __construct(CourseConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Display conversion verification queue for Dosen Pembimbing.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->get('tab', 'queue');

        $query = CourseConversion::with(['course', 'student', 'internshipApplication.partnerInstitution']);

        if (! $user->hasRole('SUPERADMIN')) {
            $query->whereHas('internshipApplication', fn ($app) => $app->where('advisor_id', $user->id));
        }

        if ($tab === 'queue') {
            $query->where('status', CourseConversion::STATUS_APPROVED_DOSEN_MK);
        } else {
            $query->where('status', '!=', CourseConversion::STATUS_APPROVED_DOSEN_MK);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('student', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('identity_number', 'like', $term))
                  ->orWhereHas('course', fn ($cq) => $cq->where('name', 'like', $term)->orWhere('code', 'like', $term));
            });
        }

        $conversions = $query->latest()->paginate(10)->withQueryString();

        $pendingCountQuery = CourseConversion::where('status', CourseConversion::STATUS_APPROVED_DOSEN_MK);
        if (! $user->hasRole('SUPERADMIN')) {
            $pendingCountQuery->whereHas('internshipApplication', fn ($app) => $app->where('advisor_id', $user->id));
        }
        $pendingCount = $pendingCountQuery->count();

        return view('academic.conversions.index', compact('conversions', 'tab', 'pendingCount'));
    }

    /**
     * Show conversion details for Dosbing verification.
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

        return view('academic.conversions.show', compact('conversion'));
    }

    /**
     * Verify or reject the conversion request.
     */
    public function review(ReviewConversionRequest $request, CourseConversion $conversion): RedirectResponse
    {
        $this->conversionService->verifyByDosbing(
            $conversion,
            $request->user(),
            $request->action,
            $request->notes,
            $request->rejection_reason
        );

        $actionWord = in_array(strtoupper($request->action), ['APPROVE', 'VERIFY'], true) ? 'diverifikasi' : 'ditolak';

        return redirect()->route('academic.conversions.index')
            ->with('success', "Pengajuan konversi mata kuliah berhasil {$actionWord}.");
    }
}
