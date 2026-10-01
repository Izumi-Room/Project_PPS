<?php

namespace App\Http\Controllers\Seminar;

use App\Http\Controllers\Controller;
use App\Models\InternshipSeminar;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentSeminarController extends Controller
{
    /**
     * Display list of seminars for the authenticated student.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $seminars = InternshipSeminar::with(['course', 'dosenMk', 'dosbing', 'kaprodi', 'wadek1'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('seminars.index', compact('seminars'));
    }

    /**
     * Show detail of student's seminar.
     */
    public function show(Request $request, InternshipSeminar $seminar): View
    {
        // Enforce authorization: student can only view their own seminar
        if ($seminar->user_id !== $request->user()->id && ! $request->user()->hasRole('SUPERADMIN')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat seminar ini.');
        }

        $seminar->loadMissing(['course', 'dosenMk', 'dosbing', 'kaprodi', 'wadek1', 'courseConversion']);

        return view('seminars.show', compact('seminar'));
    }
}
