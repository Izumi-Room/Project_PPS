<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123!');

        $users = [
            [
                'email' => 'superadmin@magang.ac.id',
                'name' => 'Super Administrator',
                'roles' => ['SUPERADMIN'],
            ],
            [
                'email' => 'kaprodi@magang.ac.id',
                'name' => 'Prof. Dr. Ir. H. Ahmad Fauzi, M.T. (Kaprodi)',
                'roles' => ['KAPRODI'],
            ],
            [
                'email' => 'mhs@magang.ac.id',
                'name' => 'Budi Santoso (Mahasiswa)',
                'roles' => ['MHS'],
            ],
            [
                'email' => 'tu@magang.ac.id',
                'name' => 'Siti Aminah, S.Kom (Tata Usaha)',
                'roles' => ['TU'],
            ],
            [
                'email' => 'dosbing@magang.ac.id',
                'name' => 'Dr. Hendra Gunawan, S.T., M.Kom (Dosen Pembimbing)',
                'roles' => ['DOSBING'],
            ],
            [
                'email' => 'dosenmk@magang.ac.id',
                'name' => 'Dr. Maya Kartika, M.Cs (Dosen Mata Kuliah)',
                'roles' => ['DOSEN_MK'],
            ],
            [
                'email' => 'wadek1@magang.ac.id',
                'name' => 'Dr. Ir. Rahmat Hidayat, M.Sc (Wakil Dekan 1)',
                'roles' => ['WADEK1'],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                ]
            );

            if (! empty($userData['roles'])) {
                $user->syncRoles($userData['roles']);
            } else {
                $user->roles()->detach();
            }
        }
    }
}
