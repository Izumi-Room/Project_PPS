<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\InternshipPeriodRequest;
use App\Models\InternshipPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipPeriodController extends Controller
{
    /**
     * Display a listing of internship periods.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = InternshipPeriod::query();

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('academic_year')) {
            $query->academicYear($request->string('academic_year'));
        }

        if ($request->filled('semester_type')) {
            $query->where('semester_type', $request->string('semester_type'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $periods = $query->orderByDesc('start_date')->paginate(10)->withQueryString();
        $academicYears = InternshipPeriod::select('academic_year')->distinct()->pluck('academic_year');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $periods,
            ]);
        }

        return view('master.internship-periods.index', compact('periods', 'academicYears'));
    }

    /**
     * Show the form for creating a new period.
     */
    public function create(): View
    {
        return view('master.internship-periods.create');
    }

    /**
     * Store a newly created period.
     */
    public function store(InternshipPeriodRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        // If setting this period to active, optionally deactivate other periods to keep single active registration period
        if (!empty($data['is_active'])) {
            InternshipPeriod::where('is_active', true)->update(['is_active' => false]);
        }

        $period = InternshipPeriod::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Periode magang berhasil ditambahkan.',
                'data' => $period,
            ], 201);
        }

        return redirect()
            ->route('master.internship-periods.index')
            ->with('success', 'Periode magang ' . $period->name . ' berhasil ditambahkan.');
    }

    /**
     * Display the specified period.
     */
    public function show(Request $request, InternshipPeriod $internshipPeriod): View|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $internshipPeriod,
            ]);
        }

        return view('master.internship-periods.show', compact('internshipPeriod'));
    }

    /**
     * Show the form for editing the specified period.
     */
    public function edit(InternshipPeriod $internshipPeriod): View
    {
        return view('master.internship-periods.edit', compact('internshipPeriod'));
    }

    /**
     * Update the specified period.
     */
    public function update(InternshipPeriodRequest $request, InternshipPeriod $internshipPeriod): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['is_active'])) {
            InternshipPeriod::where('id', '!=', $internshipPeriod->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $internshipPeriod->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Periode magang berhasil diperbarui.',
                'data' => $internshipPeriod,
            ]);
        }

        return redirect()
            ->route('master.internship-periods.index')
            ->with('success', 'Periode magang ' . $internshipPeriod->name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified period.
     */
    public function destroy(Request $request, InternshipPeriod $internshipPeriod): RedirectResponse|JsonResponse
    {
        $internshipPeriod->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Periode magang berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('master.internship-periods.index')
            ->with('success', 'Periode magang berhasil dihapus.');
    }

    /**
     * Toggle active status. Only one active period for new registrations.
     */
    public function toggleStatus(Request $request, InternshipPeriod $internshipPeriod): RedirectResponse|JsonResponse
    {
        $targetStatus = !$internshipPeriod->is_active;

        if ($targetStatus) {
            // Activate this one and deactivate others
            InternshipPeriod::where('id', '!=', $internshipPeriod->id)->update(['is_active' => false]);
            $internshipPeriod->update(['is_active' => true]);
            $message = "Periode magang '{$internshipPeriod->name}' diaktifkan sebagai periode pendaftaran aktif.";
        } else {
            $internshipPeriod->update(['is_active' => false]);
            $message = "Periode magang '{$internshipPeriod->name}' dinonaktifkan.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $internshipPeriod->is_active,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Dedicated endpoint for fetching active period(s) for registration eligibility check.
     */
    public function getActivePeriod(): JsonResponse
    {
        $activePeriod = InternshipPeriod::active()->first();

        return response()->json([
            'success' => true,
            'has_active_period' => !is_null($activePeriod),
            'data' => $activePeriod,
        ]);
    }
}
