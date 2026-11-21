@extends('layouts.app')

@section('title', 'Pengelolaan Akun Admin')
@section('page-title', 'Pengelolaan Akun Admin')
@section('page-breadcrumb', 'SCOMAR / Administrasi / Pengelolaan Akun')

@push('styles')
<style>
/* ── RESPONSIVE ACCOUNTS MASTER ADMIN ────────────────────── */
@media (max-width: 1024px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .page-toolbar {
        flex-wrap: wrap;
        gap: 8px;
    }
}
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 8px;
    }
    .page-toolbar {
        width: 100%;
        flex-wrap: wrap;
        gap: 6px;
    }
    .page-toolbar input[type="text"],
    .page-toolbar input[type="search"],
    .page-toolbar select {
        width: 100%;
        min-width: unset;
    }
    .page-toolbar .btn {
        width: 100%;
        justify-content: center;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    table.table {
        min-width: 600px;
    }
    .modal-dialog {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }
}
@media (max-width: 640px) {
    .page-header-title {
        font-size: 16px;
    }
    .page-header-sub {
        font-size: 11px;
    }
    .table th, .table td {
        font-size: 11px;
        padding: 6px 8px;
    }
    .btn {
        font-size: 11px;
        padding: 5px 10px;
    }
    .modal-body {
        padding: 12px;
    }
    .modal-header {
        padding: 10px 12px;
    }
    .modal-footer {
        padding: 8px 12px;
        flex-wrap: wrap;
        gap: 6px;
    }
}
@media (max-width: 480px) {
    .modal-dialog {
        max-width: calc(100vw - 10px);
        margin: 5px auto;
    }
    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }
    .form-group label {
        font-size: 11px;
    }
    .form-control {
        font-size: 12px;
        padding: 6px 8px;
    }
}
@media (max-width: 380px) {
    .table th, .table td {
        font-size: 10px;
        padding: 4px 6px;
    }
    .btn {
        font-size: 10px;
        padding: 4px 8px;
    }
}
</style>
@endpush

@section('content')

<div x-data="{
    showCreateModal: false,
    showEditModal: false,
    showDeleteModal: false,
    editAccount: { id: '', name: '', email: '', role: 'admin' },
    deleteAccount: { id: '', name: '', role: '' },

    openEdit(acc) {
        this.editAccount = { ...acc };
        this.showEditModal = true;
    },
    openDelete(acc) {
        this.deleteAccount = { ...acc };
        this.showDeleteModal = true;
    }
}">

    {{-- ── 1. HEADER HALAMAN ────────────────────────────────────────── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:var(--text-primary); margin:0 0 6px 0;">
                Manajemen Akun Administrator & Pengguna
            </h1>
            <p style="font-size:13.5px; color:var(--text-secondary); margin:0;">
                Kelola hak akses sistem SCOMAR. Hak pengelolaan ini hanya dimiliki oleh <strong>Master Admin</strong>.
            </p>
        </div>
        <button
            type="button"
            class="btn-scomar-primary"
            @click="showCreateModal = true"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Akun Baru</span>
        </button>
    </div>

    {{-- ── 2. KARTU STATISTIK ROLE ─────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
        <div style="background:var(--bg-surface); padding:18px 20px; border-radius:12px; border:1px solid var(--border-subtle); box-shadow:var(--card-shadow);">
            <div style="font-size:11.5px; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px;">Total Pengguna</div>
            <div style="font-size:26px; font-weight:800; color:var(--text-primary); margin-top:4px;">{{ $stats['total'] }}</div>
        </div>
        <div style="background:var(--bg-surface); padding:18px 20px; border-radius:12px; border:1px solid var(--border-subtle); box-shadow:var(--card-shadow); border-left:4px solid #73343A;">
            <div style="font-size:11.5px; font-weight:700; color:#73343A; text-transform:uppercase; letter-spacing:0.5px;">Master Admin</div>
            <div style="font-size:26px; font-weight:800; color:var(--text-primary); margin-top:4px;">{{ $stats['master_admin'] }}</div>
        </div>
        <div style="background:var(--bg-surface); padding:18px 20px; border-radius:12px; border:1px solid var(--border-subtle); box-shadow:var(--card-shadow); border-left:4px solid #D97706;">
            <div style="font-size:11.5px; font-weight:700; color:#D97706; text-transform:uppercase; letter-spacing:0.5px;">Admin Lapangan</div>
            <div style="font-size:26px; font-weight:800; color:var(--text-primary); margin-top:4px;">{{ $stats['admin'] }}</div>
        </div>
        <div style="background:var(--bg-surface); padding:18px 20px; border-radius:12px; border:1px solid var(--border-subtle); box-shadow:var(--card-shadow); border-left:4px solid #0284C7;">
            <div style="font-size:11.5px; font-weight:700; color:#0284C7; text-transform:uppercase; letter-spacing:0.5px;">Pengguna / Nelayan</div>
            <div style="font-size:26px; font-weight:800; color:var(--text-primary); margin-top:4px;">{{ $stats['user'] }}</div>
        </div>
    </div>

    {{-- ── 3. TOOLBAR FILTER & SEARCH ───────────────────────────────── --}}
    <div style="background:var(--bg-surface); border:1px solid var(--border-subtle); border-radius:12px; padding:16px 20px; margin-bottom:20px; box-shadow:var(--card-shadow);">
        <form method="GET" action="{{ route('master-admin.accounts.index') }}" style="display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:space-between;">
            <div style="display:flex; flex:1; min-width:260px; max-width:440px; position:relative;">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama atau email akun..."
                    class="scomar-input"
                    style="padding-left:36px;"
                >
                <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
                <select name="role" class="scomar-select" style="min-width:160px;" onchange="this.form.submit()">
                    <option value="">Semua Peran</option>
                    <option value="master_admin" {{ request('role') === 'master_admin' ? 'selected' : '' }}>Master Admin</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Pengguna</option>
                </select>

                <button type="submit" class="btn-scomar-secondary" style="padding:8px 14px;">
                    Filter
                </button>

                @if(request('search') || request('role'))
                    <a href="{{ route('master-admin.accounts.index') }}" class="btn-scomar-secondary" style="padding:8px 14px; color:#DC2626 !important;" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── 4. TABEL DAFTAR AKUN ─────────────────────────────────────── --}}
    <div style="background:var(--bg-surface); border:1px solid var(--border-subtle); border-radius:12px; overflow:hidden; box-shadow:var(--card-shadow);">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13.5px;">
                <thead>
                    <tr style="background:rgba(165, 184, 208, 0.12); border-bottom:1px solid var(--border-subtle); color:var(--text-primary);">
                        <th style="padding:12px 18px; font-weight:700; width:50px;">No</th>
                        <th style="padding:12px 18px; font-weight:700;">Nama Pengguna</th>
                        <th style="padding:12px 18px; font-weight:700;">Email</th>
                        <th style="padding:12px 18px; font-weight:700;">Peran / Hak Akses</th>
                        <th style="padding:12px 18px; font-weight:700;">Tanggal Daftar</th>
                        <th style="padding:12px 18px; font-weight:700; text-align:center; width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $u)
                        <tr style="border-bottom:1px solid var(--border-subtle); transition:background 0.15s;" onmouseover="this.style.background='rgba(165,184,208,0.06)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:14px 18px; color:var(--text-muted); font-size:12.5px;">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td style="padding:14px 18px; font-weight:600; color:var(--text-primary);">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:34px; height:34px; border-radius:50%; background:var(--merlot); color:#FFFFFF; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div>{{ $u->name }}</div>
                                        @if($u->id === auth()->id())
                                            <span style="font-size:10px; background:rgba(16,185,129,0.12); color:#059669; padding:2px 6px; border-radius:4px; font-weight:700;">(Akun Anda)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding:14px 18px; color:var(--text-secondary);">
                                {{ $u->email }}
                            </td>
                            <td style="padding:14px 18px;">
                                @if($u->role === 'master_admin')
                                    <span style="display:inline-flex; align-items:center; gap:6px; background:rgba(115,52,58,0.12); color:#73343A; padding:4px 10px; border-radius:6px; font-size:11.5px; font-weight:700; border:1px solid rgba(115,52,58,0.25);">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#73343A;"></span>
                                        Master Admin
                                    </span>
                                @elseif($u->role === 'admin')
                                    <span style="display:inline-flex; align-items:center; gap:6px; background:rgba(217,119,6,0.12); color:#D97706; padding:4px 10px; border-radius:6px; font-size:11.5px; font-weight:700; border:1px solid rgba(217,119,6,0.25);">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#D97706;"></span>
                                        Admin
                                    </span>
                                @else
                                    <span style="display:inline-flex; align-items:center; gap:6px; background:rgba(2,132,199,0.1); color:#0284C7; padding:4px 10px; border-radius:6px; font-size:11.5px; font-weight:600; border:1px solid rgba(2,132,199,0.2);">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#0284C7;"></span>
                                        Pengguna
                                    </span>
                                @endif
                            </td>
                            <td style="padding:14px 18px; color:var(--text-muted); font-size:12.5px;">
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <div style="display:inline-flex; gap:6px; justify-content:center;">
                                    {{-- Edit --}}
                                    <button
                                        type="button"
                                        class="btn-action-icon edit"
                                        title="Edit Akun"
                                        @click="openEdit({
                                            id: '{{ $u->id }}',
                                            name: '{{ addslashes($u->name) }}',
                                            email: '{{ addslashes($u->email) }}',
                                            role: '{{ $u->role }}'
                                        })"
                                    >
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    {{-- Hapus (Cegah hapus diri sendiri) --}}
                                    @if($u->id !== auth()->id())
                                        <button
                                            type="button"
                                            class="btn-action-icon delete"
                                            title="Hapus Akun"
                                            @click="openDelete({
                                                id: '{{ $u->id }}',
                                                name: '{{ addslashes($u->name) }}',
                                                role: '{{ $u->role }}'
                                            })"
                                        >
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:36px; text-align:center; color:var(--text-muted);">
                                Tidak ditemukan data akun yang sesuai dengan pencarian atau filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding:16px 20px; border-top:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:12.5px; color:var(--text-muted);">
                    Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} akun
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ── 5. MODAL TAMBAH AKUN BARU ────────────────────────────────── --}}
    <div
        class="scomar-modal-backdrop"
        x-show="showCreateModal"
        x-transition.opacity
        style="display:none;"
        @keydown.escape.window="showCreateModal = false"
    >
        <div class="scomar-modal-card" @click.outside="showCreateModal = false">
            <div class="scomar-modal-header">
                <div class="scomar-modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--merlot)" stroke-width="2.5">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <span>Tambah Akun Baru</span>
                </div>
                <button type="button" class="scomar-alert-close" @click="showCreateModal = false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('master-admin.accounts.store') }}">
                @csrf
                <div class="scomar-modal-body">
                    <div class="scomar-form-group">
                        <label class="scomar-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="name" class="scomar-input" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="scomar-form-group">
                        <label class="scomar-label">Alamat Email <span class="req">*</span></label>
                        <input type="email" name="email" class="scomar-input" placeholder="contoh: admin@scomar.id" required>
                    </div>

                    <div class="scomar-form-group">
                        <label class="scomar-label">Kata Sandi <span class="req">*</span></label>
                        <input type="password" name="password" class="scomar-input" placeholder="Minimal 6 karakter" minlength="6" required>
                    </div>

                    <div class="scomar-form-group" style="margin-bottom:0;">
                        <label class="scomar-label">Peran / Hak Akses <span class="req">*</span></label>
                        <select name="role" class="scomar-select" required>
                            <option value="admin" selected>Admin (Dapat CRUD data oseanografi & tangkapan)</option>
                            <option value="user">Pengguna / Nelayan (Hanya melihat data)</option>
                            <option value="master_admin">Master Admin (CRUD seluruh data & kelola akun)</option>
                        </select>
                    </div>
                </div>

                <div class="scomar-modal-footer">
                    <button type="button" class="btn-scomar-secondary" @click="showCreateModal = false">Batal</button>
                    <button type="submit" class="btn-scomar-primary">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 6. MODAL EDIT AKUN ───────────────────────────────────────── --}}
    <div
        class="scomar-modal-backdrop"
        x-show="showEditModal"
        x-transition.opacity
        style="display:none;"
        @keydown.escape.window="showEditModal = false"
    >
        <div class="scomar-modal-card" @click.outside="showEditModal = false">
            <div class="scomar-modal-header">
                <div class="scomar-modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--merlot)" stroke-width="2.5">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Edit Data Akun</span>
                </div>
                <button type="button" class="scomar-alert-close" @click="showEditModal = false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <form method="POST" :action="`{{ url('/master-admin/accounts') }}/${editAccount.id}`">
                @csrf
                @method('PUT')
                <div class="scomar-modal-body">
                    <div class="scomar-form-group">
                        <label class="scomar-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="name" x-model="editAccount.name" class="scomar-input" required>
                    </div>

                    <div class="scomar-form-group">
                        <label class="scomar-label">Alamat Email <span class="req">*</span></label>
                        <input type="email" name="email" x-model="editAccount.email" class="scomar-input" required>
                    </div>

                    <div class="scomar-form-group">
                        <label class="scomar-label">Ganti Kata Sandi (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" class="scomar-input" placeholder="Kosongkan jika tetap kata sandi lama" minlength="6">
                    </div>

                    <div class="scomar-form-group" style="margin-bottom:0;">
                        <label class="scomar-label">Peran / Hak Akses <span class="req">*</span></label>
                        <select name="role" x-model="editAccount.role" class="scomar-select" required>
                            <option value="admin">Admin (Dapat CRUD data)</option>
                            <option value="user">Pengguna / Nelayan (Hanya lihat)</option>
                            <option value="master_admin">Master Admin (Akses penuh)</option>
                        </select>
                    </div>
                </div>

                <div class="scomar-modal-footer">
                    <button type="button" class="btn-scomar-secondary" @click="showEditModal = false">Batal</button>
                    <button type="submit" class="btn-scomar-primary">Perbarui Akun</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 7. MODAL KONFIRMASI HAPUS ────────────────────────────────── --}}
    <div
        class="scomar-modal-backdrop"
        x-show="showDeleteModal"
        x-transition.opacity
        style="display:none;"
        @keydown.escape.window="showDeleteModal = false"
    >
        <div class="scomar-modal-card" style="max-width:440px;" @click.outside="showDeleteModal = false">
            <div class="scomar-modal-header" style="border-bottom:none; padding-bottom:0;">
                <div class="scomar-modal-title" style="color:#DC2626;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>Konfirmasi Hapus Akun</span>
                </div>
                <button type="button" class="scomar-alert-close" @click="showDeleteModal = false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="scomar-modal-body" style="padding-top:14px;">
                <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0 0 12px 0;">
                    Apakah Anda yakin ingin menghapus akun <strong style="color:var(--text-primary);" x-text="deleteAccount.name"></strong>?
                </p>
                <div style="background:rgba(220,38,38,0.08); border-left:3px solid #DC2626; padding:10px 14px; border-radius:6px; font-size:12px; color:#DC2626;">
                    Tindakan ini permanen. Pengguna tidak akan dapat masuk ke sistem setelah akun dihapus.
                </div>
            </div>

            <form method="POST" :action="`{{ url('/master-admin/accounts') }}/${deleteAccount.id}`">
                @csrf
                @method('DELETE')
                <div class="scomar-modal-footer">
                    <button type="button" class="btn-scomar-secondary" @click="showDeleteModal = false">Batal</button>
                    <button type="submit" class="btn-scomar-danger">Ya, Hapus Akun</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
