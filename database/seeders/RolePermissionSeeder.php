<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Roles
        $rolesData = [
            [
                'name' => 'MHS',
                'label' => 'Mahasiswa',
                'description' => 'Peserta program magang mahasiswa.',
            ],
            [
                'name' => 'TU',
                'label' => 'Tata Usaha',
                'description' => 'Staf administrasi dan verifikasi dokumen pengajuan magang.',
            ],
            [
                'name' => 'KAPRODI',
                'label' => 'Ketua Program Studi',
                'description' => 'Pimpinan prodi yang menyetujui usulan magang serta otomatis memiliki akses Superadmin.',
            ],
            [
                'name' => 'DOSBING',
                'label' => 'Dosen Pembimbing',
                'description' => 'Dosen yang membimbing dan mengevaluasi aktivitas logbook magang.',
            ],
            [
                'name' => 'DOSEN_MK',
                'label' => 'Dosen Mata Kuliah',
                'description' => 'Dosen pengampu mata kuliah magang yang melakukan penilaian akademik.',
            ],
            [
                'name' => 'WADEK1',
                'label' => 'Wakil Dekan 1',
                'description' => 'Pimpinan fakultas bidang akademik yang memonitor dan memberi rekomendasi akhir.',
            ],
            [
                'name' => 'SUPERADMIN',
                'label' => 'Super Administrator',
                'description' => 'Administrator sistem dengan hak akses penuh ke seluruh modul dan konfigurasi.',
            ],
        ];

        $roles = [];
        foreach ($rolesData as $data) {
            $roles[$data['name']] = Role::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        // 2. Define Permissions
        $permissionsData = [
            ['name' => 'view:dashboard', 'label' => 'Lihat Dashboard', 'description' => 'Akses dashboard aplikasi'],
            ['name' => 'view:profile', 'label' => 'Lihat Profil', 'description' => 'Akses dan update profil pengguna'],
            ['name' => 'access:superadmin', 'label' => 'Akses Superadmin', 'description' => 'Akses kontrol penuh panel administrator'],
            ['name' => 'manage:users', 'label' => 'Kelola Pengguna', 'description' => 'Mengelola akun pengguna dan pemberian role'],
            ['name' => 'manage:roles', 'label' => 'Kelola Role', 'description' => 'Mengonfigurasi role dan permission sistem'],
            ['name' => 'verify:documents', 'label' => 'Verifikasi Dokumen', 'description' => 'Verifikasi berkas administrasi magang'],
            ['name' => 'review:registration', 'label' => 'Review Pendaftaran', 'description' => 'Memeriksa berkas permohonan magang'],
            ['name' => 'guidance:logbook', 'label' => 'Bimbingan Logbook', 'description' => 'Membimbing dan memvalidasi logbook mahasiswa'],
            ['name' => 'evaluate:internship', 'label' => 'Evaluasi Magang', 'description' => 'Memberikan nilai evaluasi magang'],
            ['name' => 'grade:academic', 'label' => 'Penilaian Akademik', 'description' => 'Input nilai akhir mata kuliah magang'],
            ['name' => 'approve:kaprodi', 'label' => 'Persetujuan Kaprodi', 'description' => 'Persetujuan resmi dari ketua program studi'],
            ['name' => 'monitor:faculty', 'label' => 'Monitoring Fakultas', 'description' => 'Memonitor rekapitulasi magang tingkat fakultas'],
            ['name' => 'approve:faculty', 'label' => 'Persetujuan Fakultas', 'description' => 'Rekomendasi / pengesahan dari Wakil Dekan 1'],
            ['name' => 'manage:master-data', 'label' => 'Kelola Master Data', 'description' => 'Akses penuh pengelolaan master data aplikasi'],
            ['name' => 'manage:study-programs', 'label' => 'Kelola Program Studi', 'description' => 'CRUD data Program Studi'],
            ['name' => 'manage:institutions', 'label' => 'Kelola Instansi Mitra', 'description' => 'CRUD data Instansi dan Mitra Magang'],
            ['name' => 'manage:courses', 'label' => 'Kelola Mata Kuliah', 'description' => 'CRUD data Mata Kuliah Terkait Magang'],
            ['name' => 'manage:periods', 'label' => 'Kelola Periode Magang', 'description' => 'CRUD dan aktivasi Periode Magang'],
            ['name' => 'view:master-data', 'label' => 'Lihat Master Data', 'description' => 'Melihat katalog master data aktif'],
        ];

        $permissions = [];
        foreach ($permissionsData as $data) {
            $permissions[$data['name']] = Permission::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        // 3. Attach Permissions to Roles
        // MHS
        $roles['MHS']->permissions()->sync([
            $permissions['view:dashboard']->id,
            $permissions['view:profile']->id,
            $permissions['view:master-data']->id,
        ]);

        // TU
        $roles['TU']->permissions()->sync([
            $permissions['view:dashboard']->id,
            $permissions['view:profile']->id,
            $permissions['verify:documents']->id,
            $permissions['review:registration']->id,
            $permissions['manage:users']->id,
            $permissions['view:master-data']->id,
        ]);

        // DOSBING
        $roles['DOSBING']->permissions()->sync([
            $permissions['view:dashboard']->id,
            $permissions['view:profile']->id,
            $permissions['guidance:logbook']->id,
            $permissions['evaluate:internship']->id,
            $permissions['view:master-data']->id,
        ]);

        // DOSEN_MK
        $roles['DOSEN_MK']->permissions()->sync([
            $permissions['view:dashboard']->id,
            $permissions['view:profile']->id,
            $permissions['grade:academic']->id,
            $permissions['evaluate:internship']->id,
            $permissions['view:master-data']->id,
        ]);

        // WADEK1
        $roles['WADEK1']->permissions()->sync([
            $permissions['view:dashboard']->id,
            $permissions['view:profile']->id,
            $permissions['monitor:faculty']->id,
            $permissions['approve:faculty']->id,
            $permissions['view:master-data']->id,
        ]);

        // KAPRODI (inherits SUPERADMIN, plus specific approval permission)
        $roles['KAPRODI']->permissions()->sync([
            $permissions['view:dashboard']->id,
            $permissions['view:profile']->id,
            $permissions['approve:kaprodi']->id,
            $permissions['review:registration']->id,
            $permissions['access:superadmin']->id,
            $permissions['manage:users']->id,
            $permissions['manage:roles']->id,
            $permissions['manage:master-data']->id,
            $permissions['manage:study-programs']->id,
            $permissions['manage:institutions']->id,
            $permissions['manage:courses']->id,
            $permissions['manage:periods']->id,
            $permissions['view:master-data']->id,
        ]);

        // SUPERADMIN has all permissions
        $roles['SUPERADMIN']->permissions()->sync(
            collect($permissions)->pluck('id')->toArray()
        );
    }
}
