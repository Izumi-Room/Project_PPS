<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\InternshipPeriod;
use App\Models\PartnerInstitution;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Study Programs
        $prodis = [
            [
                'code' => 'TI',
                'name' => 'Teknik Informatika',
                'degree_level' => 'S1',
                'faculty' => 'Fakultas Teknik',
                'is_active' => true,
                'description' => 'Program studi sarjana fokus rekayasa perangkat lunak, AI, dan sistem terdistribusi.',
            ],
            [
                'code' => 'SI',
                'name' => 'Sistem Informasi',
                'degree_level' => 'S1',
                'faculty' => 'Fakultas Teknik',
                'is_active' => true,
                'description' => 'Program studi sarjana fokus tata kelola TI, analisis bisnis, dan enterprise system.',
            ],
            [
                'code' => 'TE',
                'name' => 'Teknik Elektro',
                'degree_level' => 'S1',
                'faculty' => 'Fakultas Teknik',
                'is_active' => true,
                'description' => 'Program studi sarjana fokus sistem tenaga, telekomunikasi, dan sistem embedded/IoT.',
            ],
        ];

        $prodiModels = [];
        foreach ($prodis as $prodiData) {
            $prodiModels[$prodiData['code']] = StudyProgram::updateOrCreate(
                ['code' => $prodiData['code']],
                $prodiData
            );
        }

        // 2. Seed Partner Institutions
        $institutions = [
            [
                'name' => 'PT Telkom Indonesia (Persero) Tbk',
                'address' => 'Jl. Japati No. 1, Bandung, Jawa Barat 40133',
                'contact_person' => 'Bambang Supriyadi (HC Talent Acquisition)',
                'email' => 'internship@telkom.co.id',
                'phone' => '022-4521400',
                'website' => 'https://www.telkom.co.id',
                'sector' => 'BUMN',
                'is_active' => true,
                'description' => 'Badan usaha milik negara bergerak di bidang telekomunikasi dan jaringan digital.',
            ],
            [
                'name' => 'PT Bank Central Asia Tbk',
                'address' => 'Menara BCA, Grand Indonesia, Jl. M.H. Thamrin No. 1, Jakarta Pusat',
                'contact_person' => 'Ratna Dewi (Campus Relations)',
                'email' => 'recruitment_it@bca.co.id',
                'phone' => '021-23588000',
                'website' => 'https://www.bca.co.id',
                'sector' => 'Swasta',
                'is_active' => true,
                'description' => 'Institusi perbankan swasta terbesar di Indonesia dengan divisi IT dan digital solution mutakhir.',
            ],
            [
                'name' => 'Dinas Komunikasi dan Informatika Provinsi',
                'address' => 'Komplek Perkantoran Pemprov, Gedung B Lt. 3',
                'contact_person' => 'Hendro Prasetyo, M.T. (Kasi Aplikasi Informatika)',
                'email' => 'diskominfo@pemprov.go.id',
                'phone' => '021-5001234',
                'website' => 'https://diskominfo.go.id',
                'sector' => 'Pemerintah',
                'is_active' => true,
                'description' => 'Instansi pemerintah daerah pengelola infrastruktur digital, portal satu data, dan SPBE.',
            ],
            [
                'name' => 'PT Aplikasi Karya Anak Bangsa (GoTo)',
                'address' => 'Pasaraya Blok M Gedung B Lt. 6-7, Jakarta Selatan',
                'contact_person' => 'Sarah Wijaya (Tech Internship Lead)',
                'email' => 'earlycareers@goto.com',
                'phone' => '021-29101070',
                'website' => 'https://www.gotocompany.com',
                'sector' => 'Startup',
                'is_active' => true,
                'description' => 'Perusahaan ekosistem digital on-demand dan e-commerce terdepan di Asia Tenggara.',
            ],
        ];

        foreach ($institutions as $instData) {
            PartnerInstitution::updateOrCreate(
                ['name' => $instData['name']],
                $instData
            );
        }

        // 3. Seed Courses
        $courses = [
            [
                'study_program_id' => $prodiModels['TI']->id,
                'code' => 'IF601',
                'name' => 'Kerja Praktik / Magang Industri',
                'credits' => 3,
                'semester' => 6,
                'is_active' => true,
                'description' => 'Mata kuliah implementasi praktik magang di industri bagi mahasiswa semester 6.',
            ],
            [
                'study_program_id' => $prodiModels['TI']->id,
                'code' => 'IF602',
                'name' => 'Seminar & Evaluasi Magang',
                'credits' => 1,
                'semester' => 6,
                'is_active' => true,
                'description' => 'Sidang presentasi laporan akhir magang dan evaluasi bimbingan.',
            ],
            [
                'study_program_id' => $prodiModels['SI']->id,
                'code' => 'SI601',
                'name' => 'Praktik Kerja Lapangan (PKL)',
                'credits' => 3,
                'semester' => 6,
                'is_active' => true,
                'description' => 'Praktik kerja bidang implementasi dan evaluasi sistem informasi bisnis.',
            ],
            [
                'study_program_id' => $prodiModels['TE']->id,
                'code' => 'TE601',
                'name' => 'Magang Keteknikan Elektro',
                'credits' => 3,
                'semester' => 6,
                'is_active' => true,
                'description' => 'Magang aplikasi bidang ketenagalistrikan, elektronika, atau otomasi industri.',
            ],
        ];

        foreach ($courses as $courseData) {
            Course::updateOrCreate(
                ['code' => $courseData['code']],
                $courseData
            );
        }

        // 4. Seed Internship Periods
        $periods = [
            [
                'name' => 'Magang Semester Genap 2025/2026 (Selesai)',
                'academic_year' => '2025/2026',
                'semester_type' => 'GENAP',
                'start_date' => '2026-02-01',
                'end_date' => '2026-06-30',
                'is_active' => false,
                'description' => 'Periode magang semester genap tahun ajaran lalu (telah ditutup).',
            ],
            [
                'name' => 'Magang Semester Ganjil 2026/2027 (Arsip)',
                'academic_year' => '2026/2027',
                'semester_type' => 'GANJIL',
                'start_date' => '2026-08-01',
                'end_date' => '2026-12-31',
                'is_active' => false,
                'description' => 'Periode magang semester ganjil (telah selesai).',
            ],
            [
                'name' => 'Magang Semester Genap 2026/2027 (Aktif)',
                'academic_year' => '2026/2027',
                'semester_type' => 'GENAP',
                'start_date' => '2027-02-01',
                'end_date' => '2027-07-31',
                'is_active' => true,
                'description' => 'Periode aktif saat ini. Seluruh pengajuan magang baru wajib memilih periode ini.',
            ],
        ];

        foreach ($periods as $periodData) {
            InternshipPeriod::updateOrCreate(
                ['name' => $periodData['name']],
                $periodData
            );
        }

        // 5. Connect existing users to Prodi if appropriate
        $mhs = User::where('email', 'mhs@magang.ac.id')->first();
        if ($mhs && empty($mhs->study_program_id)) {
            $mhs->update([
                'study_program_id' => $prodiModels['TI']->id,
                'identifier_number' => '20260801001',
                'phone' => '081234567890',
                'is_active' => true,
            ]);
        }

        $kaprodi = User::where('email', 'kaprodi@magang.ac.id')->first();
        if ($kaprodi && empty($kaprodi->study_program_id)) {
            $kaprodi->update([
                'study_program_id' => $prodiModels['TI']->id,
                'identifier_number' => '198205102008121001',
                'phone' => '081298765432',
                'is_active' => true,
            ]);
        }
    }
}
