<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    /**
     * Menampilkan daftar akun Admin & Pengguna (Khusus Master Admin).
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Pencarian nama atau email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter role
        if ($role = $request->input('role')) {
            if (in_array($role, ['master_admin', 'admin', 'user'], true)) {
                $query->where('role', $role);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total'        => User::count(),
            'master_admin' => User::where('role', 'master_admin')->count(),
            'admin'        => User::where('role', 'admin')->count(),
            'user'         => User::where('role', 'user')->count(),
        ];

        return view('master-admin.accounts.index', compact('users', 'stats'));
    }

    /**
     * Menyimpan akun baru (Admin / User).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(6)],
            'role'     => ['required', 'string', Rule::in(['admin', 'user', 'master_admin'])],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Alamat email sudah terdaftar di sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 6 karakter.',
            'role.required'     => 'Peran/Role akun wajib ditentukan.',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return redirect()->back()
            ->with('success', "Akun {$validated['name']} ({$validated['role']}) berhasil ditambahkan!");
    }

    /**
     * Memperbarui data akun.
     */
    public function update(Request $request, User $account): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($account->id)],
            'password' => ['nullable', 'string', Password::min(6)],
            'role'     => ['required', 'string', Rule::in(['admin', 'user', 'master_admin'])],
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique'   => 'Alamat email sudah digunakan oleh akun lain.',
            'role.required'  => 'Peran/Role akun wajib ditentukan.',
        ]);

        // Cegah Master Admin menurunkan perannya sendiri jika dia satu-satunya Master Admin
        if ($account->id === auth()->id() && $validated['role'] !== 'master_admin') {
            $masterCount = User::where('role', 'master_admin')->count();
            if ($masterCount <= 1) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat mengubah role karena Anda adalah satu-satunya Master Admin di sistem.');
            }
        }

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $account->update($data);

        return redirect()->back()
            ->with('success', "Data akun {$account->name} berhasil diperbarui!");
    }

    /**
     * Menghapus akun dari sistem.
     */
    public function destroy(User $account): RedirectResponse
    {
        // Cegah menghapus diri sendiri
        if ($account->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        // Cegah menghapus jika Master Admin terakhir
        if ($account->isMasterAdmin() && User::where('role', 'master_admin')->count() <= 1) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus Master Admin terakhir di sistem.');
        }

        $name = $account->name;
        $account->delete();

        return redirect()->back()
            ->with('success', "Akun {$name} berhasil dihapus dari sistem.");
    }
}
