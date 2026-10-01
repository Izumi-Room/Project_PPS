<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internship\RespondAdvisorAssignmentRequest;
use App\Models\InternshipAdvisorAssignment;
use App\Services\Internship\AdvisorAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DosenAdvisorAssignmentController extends Controller
{
    protected AdvisorAssignmentService $assignmentService;

    public function __construct(AdvisorAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display advisor assignment requests for logged in lecturer.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = InternshipAdvisorAssignment::with([
            'application.student.studyProgram',
            'application.partnerInstitution',
            'application.internshipPeriod',
            'application.studyProgram',
            'application.documents',
            'assigner',
        ]);

        // If not superadmin, restrict strictly to current lecturer
        if (! $user->hasRole('SUPERADMIN')) {
            $query->forAdvisor($user->id);
        }

        $activeTab = $request->query('tab', 'pending');

        if ($activeTab === 'pending') {
            $query->pending();
        } elseif ($activeTab === 'accepted') {
            $query->accepted();
        } elseif ($activeTab === 'rejected') {
            $query->rejected();
        }

        $assignments = $query->latest()->paginate(10)->withQueryString();

        $pendingCount = InternshipAdvisorAssignment::when(! $user->hasRole('SUPERADMIN'), fn ($q) => $q->forAdvisor($user->id))
            ->pending()
            ->count();

        $acceptedCount = InternshipAdvisorAssignment::when(! $user->hasRole('SUPERADMIN'), fn ($q) => $q->forAdvisor($user->id))
            ->accepted()
            ->count();

        return view('academic.advisors.index', compact('assignments', 'activeTab', 'pendingCount', 'acceptedCount'));
    }

    /**
     * Respond to assignment request (Accept / Reject).
     */
    public function respond(RespondAdvisorAssignmentRequest $request, InternshipAdvisorAssignment $assignment): RedirectResponse
    {
        $decision = $request->input('decision');
        $reason = $request->input('reason');

        $this->assignmentService->respondAssignment(
            $assignment,
            Auth::user(),
            $decision,
            $reason
        );

        $msg = $decision === 'ACCEPT'
            ? 'Penugasan bimbingan magang berhasil diterima. Mahasiswa telah masuk ke daftar bimbingan aktif Anda.'
            : 'Penugasan bimbingan magang telah ditolak dan dikembalikan ke Kaprodi.';

        return redirect()->route('academic.advisor-assignments.index')->with('success', $msg);
    }
}
