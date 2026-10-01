<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\StoreSubmissionComponentRequest;
use App\Http\Requests\Submission\UpdateSubmissionComponentRequest;
use App\Models\Course;
use App\Models\SubmissionComponent;
use App\Services\Submission\DynamicSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosenMkComponentController extends Controller
{
    protected DynamicSubmissionService $submissionService;

    public function __construct(DynamicSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Display a listing of submission components.
     */
    public function index(Request $request): View
    {
        $courses = Course::where('is_active', true)->orderBy('name', 'asc')->get();
        $selectedCourseId = $request->get('course_id', $courses->first()?->id);

        $components = collect();
        if ($selectedCourseId) {
            $components = SubmissionComponent::where('course_id', $selectedCourseId)
                ->with(['course', 'creator'])
                ->withCount('submissions')
                ->latest()
                ->paginate(15)
                ->withQueryString();
        }

        return view('dosen-mk.components.index', compact('courses', 'selectedCourseId', 'components'));
    }

    /**
     * Show form for creating a new component.
     */
    public function create(Request $request): View
    {
        $courses = Course::where('is_active', true)->orderBy('name', 'asc')->get();
        $defaultCourseId = $request->get('course_id', $courses->first()?->id);

        return view('dosen-mk.components.create', compact('courses', 'defaultCourseId'));
    }

    /**
     * Store a newly created component in storage.
     */
    public function store(StoreSubmissionComponentRequest $request): RedirectResponse
    {
        $component = $this->submissionService->createComponent($request->user(), $request->validated());

        return redirect()->route('dosen-mk.components.index', ['course_id' => $component->course_id])
            ->with('success', "Komponen pengumpulan '{$component->name}' berhasil dibuat.");
    }

    /**
     * Show form for editing the component.
     */
    public function edit(SubmissionComponent $component): View
    {
        $courses = Course::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('dosen-mk.components.edit', compact('component', 'courses'));
    }

    /**
     * Update the component in storage.
     */
    public function update(UpdateSubmissionComponentRequest $request, SubmissionComponent $component): RedirectResponse
    {
        $this->submissionService->updateComponent($component, $request->user(), $request->validated());

        return redirect()->route('dosen-mk.components.index', ['course_id' => $component->course_id])
            ->with('success', "Komponen pengumpulan '{$component->name}' berhasil diperbarui.");
    }

    /**
     * Remove the component from storage.
     */
    public function destroy(Request $request, SubmissionComponent $component): RedirectResponse
    {
        $courseId = $component->course_id;
        $this->submissionService->deleteComponent($component, $request->user());

        return redirect()->route('dosen-mk.components.index', ['course_id' => $courseId])
            ->with('success', 'Komponen pengumpulan berhasil dihapus / dinonaktifkan.');
    }
}
