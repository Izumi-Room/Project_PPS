<?php

namespace Database\Factories;

use App\Models\InternshipApplication;
use App\Models\InternshipPeriod;
use App\Models\PartnerInstitution;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InternshipApplication>
 */
class InternshipApplicationFactory extends Factory
{
    protected $model = InternshipApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'study_program_id' => StudyProgram::first()?->id ?? StudyProgram::factory(),
            'partner_institution_id' => PartnerInstitution::first()?->id ?? PartnerInstitution::factory(),
            'internship_period_id' => InternshipPeriod::first()?->id ?? InternshipPeriod::factory(),
            'student_name' => fake()->name(),
            'student_nim' => fake()->unique()->numerify('220101####'),
            'student_phone' => '081234567890',
            'start_date' => now()->addWeeks(2)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'proposal_title' => fake()->sentence(5),
            'internship_plan' => fake()->paragraph(),
            'status' => InternshipApplication::STATUS_DRAFT,
        ];
    }
}
