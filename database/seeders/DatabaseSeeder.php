<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan database seeders untuk membuat akun awal SCOMAR:
     * 1. Master Admin (Akses penuh + kelola akun admin)
     * 2. Admin Biasa (Akses penuh + CRUD data perikanan)
     * 3. Pengguna Biasa (Hanya lihat informasi & grafik)
     */
    public function run(): void
    {
        // ── 1. AKUN MASTER ADMIN ───────────────────────────────────
        User::updateOrCreate(
            ['email' => 'master@scomar.id'],
            [
                'name'     => 'Master Admin SCOMAR',
                'password' => Hash::make('password123'),
                'role'     => 'master_admin',
            ]
        );

        // ── 2. AKUN ADMIN BIASA ────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@scomar.id'],
            [
                'name'     => 'Admin Lapangan SCOMAR',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
            ]
        );

        // ── 3. AKUN PENGGUNA BIASA (USER) ──────────────────────────
        User::updateOrCreate(
            ['email' => 'user@scomar.id'],
            [
                'name'     => 'Nelayan Pengguna SCOMAR',
                'password' => Hash::make('password123'),
                'role'     => 'user',
            ]
        );
    }
}
