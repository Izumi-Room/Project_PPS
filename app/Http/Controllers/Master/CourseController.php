<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\CourseRequest;
use App\Models\Course;
use App\Models\StudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Course::query()->with('studyProgram');

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('study_program_id')) {
            $query->studyProgram($request->input('study_program_id'));
        }

        if ($request->filled('semester')) {
            $query->semester($request->input('semester'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $courses = $query->orderBy('study_program_id')->orderBy('semester')->orderBy('code')->paginate(10)->withQueryString();
        $studyPrograms = StudyProgram::active()->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $courses,
            ]);
        }

        return view('master.courses.index', compact('courses', 'studyPrograms'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create(): View
    {
        $studyPrograms = StudyProgram::active()->orderBy('name')->get();
        return view('master.courses.create', compact('studyPrograms'));
    }

    /**
     * Store a newly created course.
     */
    public function store(CourseRequest $request): RedirectResponse|JsonResponse
    {
        $course = Course::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mata kuliah berhasil ditambahkan.',
                'data' => $course->load('studyProgram'),
            ], 201);
        }

        return redirect()
            ->route('master.courses.index')
            ->with('success', 'Mata kuliah ' . $course->code . ' - ' . $course->name . ' berhasil ditambahkan.');
    }

    /**
     * Display the specified course.
     */
    public function show(Request $request, Course $course): View|JsonResponse
    {
        $course->load('studyProgram');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $course,
            ]);
        }

        return view('master.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course): View
    {
        $studyPrograms = StudyProgram::active()->orderBy('name')->get();
        return view('master.courses.edit', compact('course', 'studyPrograms'));
    }

    /**
     * Update the specified course.
     */
    public function update(CourseRequest $request, Course $course): RedirectResponse|JsonResponse
    {
        $course->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data mata kuliah berhasil diperbarui.',
                'data' => $course->load('studyProgram'),
            ]);
        }

        return redirect()
            ->route('master.courses.index')
            ->with('success', 'Mata kuliah ' . $course->name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Request $request, Course $course): RedirectResponse|JsonResponse
    {
        $course->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mata kuliah berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('master.courses.index')
            ->with('success', 'Mata kuliah ' . $course->code . ' berhasil dihapus.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Request $request, Course $course): RedirectResponse|JsonResponse
    {
        $course->update(['is_active' => !$course->is_active]);
        $statusStr = $course->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Mata kuliah berhasil {$statusStr}.",
                'is_active' => $course->is_active,
            ]);
        }

        return back()->with('success', "Mata kuliah {$course->name} berhasil {$statusStr}.");
    }
}
