<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StudyProgramRequest;
use App\Models\StudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudyProgramController extends Controller
{
    /**
     * Display a listing of study programs.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = StudyProgram::query()->withCount('courses', 'users');

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->filled('degree')) {
            $query->where('degree_level', $request->string('degree'));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $studyPrograms = $query->orderBy('name')->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $studyPrograms,
            ]);
        }

        return view('master.study-programs.index', compact('studyPrograms'));
    }

    /**
     * Show the form for creating a new study program.
     */
    public function create(): View
    {
        return view('master.study-programs.create');
    }

    /**
     * Store a newly created study program.
     */
    public function store(StudyProgramRequest $request): RedirectResponse|JsonResponse
    {
        $studyProgram = StudyProgram::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Program studi berhasil ditambahkan.',
                'data' => $studyProgram,
            ], 201);
        }

        return redirect()
            ->route('master.study-programs.index')
            ->with('success', 'Program studi ' . $studyProgram->name . ' (' . $studyProgram->code . ') berhasil ditambahkan.');
    }

    /**
     * Display the specified study program.
     */
    public function show(Request $request, StudyProgram $studyProgram): View|JsonResponse
    {
        $studyProgram->load(['courses' => fn ($q) => $q->orderBy('semester')->orderBy('code'), 'users']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $studyProgram,
            ]);
        }

        return view('master.study-programs.show', compact('studyProgram'));
    }

    /**
     * Show the form for editing the specified study program.
     */
    public function edit(StudyProgram $studyProgram): View
    {
        return view('master.study-programs.edit', compact('studyProgram'));
    }

    /**
     * Update the specified study program.
     */
    public function update(StudyProgramRequest $request, StudyProgram $studyProgram): RedirectResponse|JsonResponse
    {
        $studyProgram->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Program studi berhasil diperbarui.',
                'data' => $studyProgram,
            ]);
        }

        return redirect()
            ->route('master.study-programs.index')
            ->with('success', 'Program studi ' . $studyProgram->name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified study program or toggle active status.
     */
    public function destroy(Request $request, StudyProgram $studyProgram): RedirectResponse|JsonResponse
    {
        // Check if there are associated courses
        if ($studyProgram->courses()->exists()) {
            // Cannot hard delete if child courses exist; deactivate instead
            $studyProgram->update(['is_active' => false]);
            $message = 'Program studi memiliki relasi mata kuliah aktif, status dinonaktifkan (deactivated).';
        } else {
            $studyProgram->delete();
            $message = 'Program studi berhasil dihapus.';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('master.study-programs.index')
            ->with('success', $message);
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Request $request, StudyProgram $studyProgram): RedirectResponse|JsonResponse
    {
        $studyProgram->update(['is_active' => !$studyProgram->is_active]);
        $statusStr = $studyProgram->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Program studi berhasil {$statusStr}.",
                'is_active' => $studyProgram->is_active,
            ]);
        }

        return back()->with('success', "Program studi {$studyProgram->name} berhasil {$statusStr}.");
    }
}
