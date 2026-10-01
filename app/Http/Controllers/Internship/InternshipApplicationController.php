<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internship\StoreInternshipApplicationRequest;
use App\Http\Requests\Internship\UpdateInternshipApplicationRequest;
use App\Http\Requests\Internship\UploadAcceptanceLetterRequest;
use App\Models\InternshipApplication;
use App\Models\InternshipDocument;
use App\Models\InternshipPeriod;
use App\Models\PartnerInstitution;
use App\Models\StudyProgram;
use App\Services\Internship\InternshipWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InternshipApplicationController extends Controller
{
    protected InternshipWorkflowService $workflowService;

    public function __construct(InternshipWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of student's applications.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = InternshipApplication::with(['partnerInstitution', 'internshipPeriod', 'studyProgram'])
            ->latest();

        // If not superadmin or staff, restrict to student's own applications
        if (! $user->hasAnyRole(['SUPERADMIN', 'TU', 'KAPRODI', 'WADEK1'])) {
            $query->where('user_id', $user->id);
        } else {
            // If staff is viewing general list, can filter by student
            if ($request->filled('student_id')) {
                $query->where('user_id', $request->student_id);
            }
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $applications = $query->paginate(10)->withQueryString();

        return view('internships.index', compact('applications'));
    }

    /**
     * Show form for creating a new internship application.
     */
    public function create(): View
    {
        $user = Auth::user();
        $activePeriods = InternshipPeriod::active()->get();
        $partnerInstitutions = PartnerInstitution::active()->orderBy('name')->get();
        $studyPrograms = StudyProgram::active()->get();

        return view('internships.create', compact('user', 'activePeriods', 'partnerInstitutions', 'studyPrograms'));
    }

    /**
     * Store new application (as Draft or Submitted).
     */
    public function store(StoreInternshipApplicationRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $isSubmit = $request->input('action') === 'submit';

        $uploadedFiles = [
            'document_transcript' => $request->file('document_transcript'),
            'document_proposal' => $request->file('document_proposal'),
            'document_cv' => $request->file('document_cv'),
            'document_parent_consent' => $request->file('document_parent_consent'),
            'document_other' => $request->file('document_other'),
        ];

        $application = $this->workflowService->saveApplication(
            $user,
            $request->validated(),
            $uploadedFiles,
            $isSubmit
        );

        $msg = $isSubmit
            ? 'Pendaftaran magang berhasil diajukan dan sedang menunggu verifikasi Tata Usaha (TU).'
            : 'Draft pendaftaran magang berhasil disimpan.';

        return redirect()->route('internships.show', $application)->with('success', $msg);
    }

    /**
     * Display the specified application detail, status history, and documents.
     */
    public function show(InternshipApplication $internship): View
    {
        $this->authorizeView($internship);

        $internship->load([
            'student.studyProgram',
            'studyProgram',
            'partnerInstitution',
            'internshipPeriod',
            'documents',
            'statusHistories.actor',
        ]);

        return view('internships.show', compact('internship'));
    }

    /**
     * Show form for editing an existing application (Draft or Incomplete).
     */
    public function edit(InternshipApplication $internship): View
    {
        $this->authorizeEdit($internship);

        $user = Auth::user();
        $activePeriods = InternshipPeriod::active()->get();
        $partnerInstitutions = PartnerInstitution::active()->orderBy('name')->get();
        $studyPrograms = StudyProgram::active()->get();

        return view('internships.edit', compact('internship', 'user', 'activePeriods', 'partnerInstitutions', 'studyPrograms'));
    }

    /**
     * Update application (Draft or Resubmission).
     */
    public function update(UpdateInternshipApplicationRequest $request, InternshipApplication $internship): RedirectResponse
    {
        $this->authorizeEdit($internship);

        $user = Auth::user();
        $isSubmit = $request->input('action') === 'submit';

        $uploadedFiles = [
            'document_transcript' => $request->file('document_transcript'),
            'document_proposal' => $request->file('document_proposal'),
            'document_cv' => $request->file('document_cv'),
            'document_parent_consent' => $request->file('document_parent_consent'),
            'document_other' => $request->file('document_other'),
        ];

        $this->workflowService->saveApplication(
            $internship->student,
            $request->validated(),
            $uploadedFiles,
            $isSubmit,
            $internship
        );

        $msg = $isSubmit
            ? 'Pendaftaran magang berhasil diajukan ulang ke Tata Usaha (TU).'
            : 'Perubahan draft pendaftaran magang berhasil disimpan.';

        return redirect()->route('internships.show', $internship)->with('success', $msg);
    }

    /**
     * Upload Surat Balasan Instansi by Student.
     */
    public function uploadAcceptance(UploadAcceptanceLetterRequest $request, InternshipApplication $internship): RedirectResponse
    {
        $this->workflowService->uploadAcceptanceLetter(
            $internship,
            Auth::user(),
            $request->file('acceptance_letter_file')
        );

        return redirect()->route('internships.show', $internship)
            ->with('success', 'Surat balasan instansi berhasil diunggah.');
    }

    /**
     * Download application attached document securely.
     */
    public function downloadDocument(InternshipApplication $internship, InternshipDocument $document)
    {
        $this->authorizeView($internship);

        if ($document->internship_application_id !== $internship->id) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan pada penyimpanan.');
        }

        return Storage::disk('local')->response(
            $document->file_path,
            $document->document_name,
            ['Content-Type' => $document->mime_type]
        );
    }

    /**
     * Download or view Surat Pengantar.
     */
    public function downloadReferenceLetter(InternshipApplication $internship)
    {
        $this->authorizeView($internship);

        if (! $internship->canDownloadReferenceLetter()) {
            abort(404, 'Surat pengantar belum diterbitkan untuk pendaftaran ini.');
        }

        // If TU uploaded a scanned PDF
        if ($internship->reference_letter_path && Storage::disk('local')->exists($internship->reference_letter_path)) {
            return Storage::disk('local')->response(
                $internship->reference_letter_path,
                "Surat_Pengantar_{$internship->student_nim}.pdf"
            );
        }

        // Otherwise generate clean printable official letter view
        return view('internships.reference-letter', compact('internship'));
    }

    /**
     * Download Surat Balasan Instansi.
     */
    public function downloadAcceptanceLetter(InternshipApplication $internship)
    {
        $this->authorizeView($internship);

        if (! $internship->acceptance_letter_path || ! Storage::disk('local')->exists($internship->acceptance_letter_path)) {
            abort(404, 'Surat balasan instansi belum diunggah.');
        }

        return Storage::disk('local')->response(
            $internship->acceptance_letter_path,
            "Surat_Balasan_Instansi_{$internship->student_nim}.pdf"
        );
    }

    /**
     * Internal authorization guard for viewing an application.
     */
    protected function authorizeView(InternshipApplication $application): void
    {
        $user = Auth::user();

        // Superadmin, TU, Kaprodi, Wadek1 can view
        if ($user->hasAnyRole(['SUPERADMIN', 'TU', 'KAPRODI', 'WADEK1'])) {
            return;
        }

        // Student owner can view
        if ($application->user_id === $user->id) {
            return;
        }

        abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk melihat pengajuan magang ini.');
    }

    /**
     * Internal authorization guard for editing an application.
     */
    protected function authorizeEdit(InternshipApplication $application): void
    {
        $user = Auth::user();

        if (! $application->isEditableByStudent()) {
            abort(403, 'Aplikasi ini sedang dalam proses verifikasi atau sudah selesai dan tidak dapat diubah.');
        }

        if ($user->hasRole('SUPERADMIN') || $application->user_id === $user->id) {
            return;
        }

        abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk mengubah pengajuan magang ini.');
    }
}
