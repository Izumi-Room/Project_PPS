<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\PartnerInstitutionRequest;
use App\Models\PartnerInstitution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerInstitutionController extends Controller
{
    /**
     * Display a listing of partner institutions.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = PartnerInstitution::query();

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('sector')) {
            $query->sector($request->string('sector'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $institutions = $query->orderBy('name')->paginate(10)->withQueryString();
        $sectors = PartnerInstitution::select('sector')->distinct()->pluck('sector');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $institutions,
            ]);
        }

        return view('master.partner-institutions.index', compact('institutions', 'sectors'));
    }

    /**
     * Show the form for creating a new institution.
     */
    public function create(): View
    {
        return view('master.partner-institutions.create');
    }

    /**
     * Store a newly created institution.
     */
    public function store(PartnerInstitutionRequest $request): RedirectResponse|JsonResponse
    {
        $institution = PartnerInstitution::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Instansi mitra berhasil ditambahkan.',
                'data' => $institution,
            ], 201);
        }

        return redirect()
            ->route('master.partner-institutions.index')
            ->with('success', 'Instansi mitra ' . $institution->name . ' berhasil ditambahkan.');
    }

    /**
     * Display the specified institution.
     */
    public function show(Request $request, PartnerInstitution $partnerInstitution): View|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $partnerInstitution,
            ]);
        }

        return view('master.partner-institutions.show', compact('partnerInstitution'));
    }

    /**
     * Show the form for editing the specified institution.
     */
    public function edit(PartnerInstitution $partnerInstitution): View
    {
        return view('master.partner-institutions.edit', compact('partnerInstitution'));
    }

    /**
     * Update the specified institution.
     */
    public function update(PartnerInstitutionRequest $request, PartnerInstitution $partnerInstitution): RedirectResponse|JsonResponse
    {
        $partnerInstitution->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data instansi mitra berhasil diperbarui.',
                'data' => $partnerInstitution,
            ]);
        }

        return redirect()
            ->route('master.partner-institutions.index')
            ->with('success', 'Data instansi ' . $partnerInstitution->name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified institution.
     */
    public function destroy(Request $request, PartnerInstitution $partnerInstitution): RedirectResponse|JsonResponse
    {
        $partnerInstitution->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data instansi berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('master.partner-institutions.index')
            ->with('success', 'Instansi ' . $partnerInstitution->name . ' berhasil dihapus.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Request $request, PartnerInstitution $partnerInstitution): RedirectResponse|JsonResponse
    {
        $partnerInstitution->update(['is_active' => !$partnerInstitution->is_active]);
        $statusStr = $partnerInstitution->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Instansi berhasil {$statusStr}.",
                'is_active' => $partnerInstitution->is_active,
            ]);
        }

        return back()->with('success', "Instansi {$partnerInstitution->name} berhasil {$statusStr}.");
    }
}
