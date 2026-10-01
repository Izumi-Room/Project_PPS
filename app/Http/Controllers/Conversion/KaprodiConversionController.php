<?php

namespace App\Http\Controllers\Conversion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseConversion\AcknowledgeConversionRequest;
use App\Models\CourseConversion;
use App\Services\CourseConversion\CourseConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaprodiConversionController extends Controller
{
    protected CourseConversionService $conversionService;

    public function __construct(CourseConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Display conversion acknowledgement queue for Kaprodi.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->get('tab', 'queue');

        $query = CourseConversion::with(['course', 'student', 'internshipApplication.studyProgram', 'internshipApplication.partnerInstitution']);

        if (! $user->hasRole('SUPERADMIN') && $user->study_program_id) {
            $query->whereHas('internshipApplication', fn ($app) => $app->where('study_program_id', $user->study_program_id));
        }

        if ($tab === 'queue') {
            $query->where('status', CourseConversion::STATUS_APPROVED_WADEK1);
        } else {
            $query->where('status', '!=', CourseConversion::STATUS_APPROVED_WADEK1);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('student', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('identity_number', 'like', $term))
                  ->orWhereHas('course', fn ($cq) => $cq->where('name', 'like', $term)->orWhere('code', 'like', $term));
            });
        }

        $conversions = $query->latest()->paginate(10)->withQueryString();

        $pendingCountQuery = CourseConversion::where('status', CourseConversion::STATUS_APPROVED_WADEK1);
        if (! $user->hasRole('SUPERADMIN') && $user->study_program_id) {
            $pendingCountQuery->whereHas('internshipApplication', fn ($app) => $app->where('study_program_id', $user->study_program_id));
        }
        $pendingCount = $pendingCountQuery->count();

        return view('kaprodi.conversions.index', compact('conversions', 'tab', 'pendingCount'));
    }

    /**
     * Show conversion details for Kaprodi acknowledgement.
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
            'wadek1',
            'statusHistories.actor',
        ]);

        return view('kaprodi.conversions.show', compact('conversion'));
    }

    /**
     * Mark the conversion as "Diketahui" (Acknowledged).
     */
    public function acknowledge(AcknowledgeConversionRequest $request, CourseConversion $conversion): RedirectResponse
    {
        $this->conversionService->acknowledgeByKaprodi(
            $conversion,
            $request->user(),
            $request->notes
        );

        return redirect()->route('kaprodi.conversions.index')
            ->with('success', 'Konversi mata kuliah berhasil ditandai Diketahui.');
    }
}
