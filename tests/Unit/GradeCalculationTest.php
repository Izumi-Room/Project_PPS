<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CourseConversion;
use App\Models\StudentSubmission;
use App\Models\SubmissionComponent;
use App\Models\User;
use App\Models\Course;
use App\Models\StudyProgram;
use App\Models\PartnerInstitution;
use App\Models\InternshipPeriod;
use App\Models\InternshipApplication;
use App\Services\Grade\GradeCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GradeCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_weighted_calculation()
    {
        $service = new GradeCalculationService();

        $user = User::factory()->create();

        $prodi = StudyProgram::create([
            'code' => 'TI',
            'name' => 'Teknik Informatika',
            'degree_level' => 'S1',
            'faculty' => 'Teknik',
            'is_active' => true,
        ]);

        $partner = PartnerInstitution::create([
            'name' => 'PT Solusi Teknologi',
            'address' => 'Jl. Sudirman No 1',
            'email' => 'contact@solusi.com',
            'sector' => 'Swasta',
            'is_active' => true,
        ]);

        $period = InternshipPeriod::create([
            'name' => 'Periode Genap 2026',
            'academic_year' => '2026/2027',
            'semester_type' => 'GENAP',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'is_active' => true,
        ]);

        $course = Course::create([
            'study_program_id' => $prodi->id,
            'code' => 'IF101',
            'name' => 'Pemrograman Web',
            'credits' => 3,
            'semester' => 5,
            'is_active' => true,
        ]);

        $app = InternshipApplication::create([
            'user_id' => $user->id,
            'study_program_id' => $prodi->id,
            'partner_institution_id' => $partner->id,
            'internship_period_id' => $period->id,
            'student_name' => 'Test Student',
            'student_nim' => '12345678',
            'student_phone' => '08123456789',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'internship_plan' => 'Rencana magang',
            'status' => 'DISETUJUI',
        ]);

        $conversion = CourseConversion::create([
            'internship_application_id' => $app->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'activity_plan' => 'Test plan',
            'status' => 'DIAJUKAN',
        ]);

        $c1 = SubmissionComponent::create([
            'course_id' => $course->id,
            'created_by' => $user->id,
            'name' => 'Comp 1',
            'submission_type' => 'FILE',
            'deadline' => now()->addDays(7),
            'weight' => 2,
            'is_required' => true,
            'is_active' => true,
        ]);

        $c2 = SubmissionComponent::create([
            'course_id' => $course->id,
            'created_by' => $user->id,
            'name' => 'Comp 2',
            'submission_type' => 'FILE',
            'deadline' => now()->addDays(7),
            'weight' => 3,
            'is_required' => true,
            'is_active' => true,
        ]);

        StudentSubmission::create([
            'submission_component_id' => $c1->id,
            'user_id' => $user->id,
            'course_conversion_id' => $conversion->id,
            'score' => 80,
            'status' => 'DISETUJUI',
        ]);

        StudentSubmission::create([
            'submission_component_id' => $c2->id,
            'user_id' => $user->id,
            'course_conversion_id' => $conversion->id,
            'score' => 90,
            'status' => 'DISETUJUI',
        ]);

        // (80*2 + 90*3) / 5 = (160 + 270) / 5 = 430 / 5 = 86
        $this->assertEquals(86, $service->calculate($conversion));
    }
}
