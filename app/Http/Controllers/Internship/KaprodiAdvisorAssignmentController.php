<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internship\AssignAdvisorRequest;
use App\Models\InternshipApplication;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\Internship\AdvisorAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KaprodiAdvisorAssignmentController extends Controller
{
    protected AdvisorAssignmentService $assignmentService;

    public function __construct(AdvisorAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display queue of students ready for advisor assignment.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = InternshipApplication::with([
            'student.studyProgram',
            'partnerInstitution',
            'internshipPeriod',
            'studyProgram',
            'advisor',
            'activeAdvisorAssignment.advisor',
        ])->where('status', InternshipApplication::STATUS_APPROVED);

        // Filter by Kaprodi study program if not superadmin
        if ($user->study_program_id && ! $user->hasRole('SUPERADMIN')) {
            $query->where('study_program_id', $user->study_program_id);
        }

        $activeTab = $request->query('tab', 'ready');

        if ($activeTab === 'ready') {
            // Queue: Mahasiswa yang siap ditentukan dosen pembimbing
            $query->readyForAdvisorAssignment();
        } else {
            // Assigned or active
            $query->whereIn('advisor_status', [
                InternshipApplication::STATUS_ADVISOR_PENDING,
                InternshipApplication::STATUS_ADVISOR_ACCEPTED,
            ]);
        }

        if ($request->filled('study_program_id')) {
            $query->filterProdi($request->study_program_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $applications = $query->latest()->paginate(10)->withQueryString();
        $studyPrograms = StudyProgram::active()->get();

        // Count students ready for assignment
        $readyCountQuery = InternshipApplication::readyForAdvisorAssignment();
        if ($user->study_program_id && ! $user->hasRole('SUPERADMIN')) {
            $readyCountQuery->where('study_program_id', $user->study_program_id);
        }
        $readyCount = $readyCountQuery->count();

        // Get active lecturers holding DOSBING role
        $availableAdvisors = User::whereHas('roles', fn ($q) => $q->where('name', 'DOSBING'))
            ->active()
            ->orderBy('name')
            ->get();

        return view('kaprodi.advisors.index', compact(
            'applications',
            'studyPrograms',
            'availableAdvisors',
            'activeTab',
            'readyCount'
        ));
    }

    /**
     * Display application detail for advisor assignment.
     */
    public function show(InternshipApplication $internship): View
    {
        $internship->load([
            'student.studyProgram',
            'studyProgram',
            'partnerInstitution',
            'internshipPeriod',
            'documents',
            'advisor',
            'advisorAssignments.advisor',
            'advisorAssignments.assigner',
            'statusHistories.actor',
        ]);

        $availableAdvisors = User::whereHas('roles', fn ($q) => $q->where('name', 'DOSBING'))
            ->active()
            ->orderBy('name')
            ->get();

        return view('kaprodi.advisors.show', compact('internship', 'availableAdvisors'));
    }

    /**
     * Submit advisor assignment.
     */
    public function assign(AssignAdvisorRequest $request, InternshipApplication $internship): RedirectResponse
    {
        $advisor = User::findOrFail($request->input('advisor_id'));

        $this->assignmentService->assignAdvisor(
            $internship,
            $advisor,
            Auth::user(),
            $request->input('notes')
        );

        return redirect()->route('kaprodi.advisors.index')->with(
            'success',
            "Dosen pembimbing {$advisor->name} berhasil ditugaskan untuk mahasiswa {$internship->student_name}. Notifikasi telah dikirimkan."
        );
    }
}
