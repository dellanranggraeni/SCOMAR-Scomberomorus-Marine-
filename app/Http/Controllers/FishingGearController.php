<?php

namespace App\Http\Controllers;

use App\Models\FishingGear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FishingGearController extends Controller
{
    /**
     * Simpan data rekomendasi alat tangkap baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'category'          => ['required', 'string', 'max:100'],
            'target_species'    => ['nullable', 'string', 'max:150'],
            'selectivity_level' => ['required', 'string', 'in:Tinggi,Sedang,Rendah'],
            'status'            => ['required', 'string', 'in:Direkomendasikan,Diizinkan,Dibatasi,Dilarang'],
            'description'       => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required'              => 'Nama alat tangkap wajib diisi.',
            'category.required'          => 'Kategori alat tangkap wajib diisi.',
            'selectivity_level.required' => 'Tingkat selektivitas wajib dipilih.',
            'status.required'            => 'Status rekomendasi wajib dipilih.',
        ]);

        FishingGear::create($validated);

        return redirect()->route('dashboard', ['#fishing-gears-section'])
            ->with('success', 'Rekomendasi alat tangkap baru berhasil ditambahkan!');
    }

    /**
     * Perbarui data alat tangkap.
     */
    public function update(Request $request, FishingGear $fishingGear): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'category'          => ['required', 'string', 'max:100'],
            'target_species'    => ['nullable', 'string', 'max:150'],
            'selectivity_level' => ['required', 'string', 'in:Tinggi,Sedang,Rendah'],
            'status'            => ['required', 'string', 'in:Direkomendasikan,Diizinkan,Dibatasi,Dilarang'],
            'description'       => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required'              => 'Nama alat tangkap wajib diisi.',
            'category.required'          => 'Kategori alat tangkap wajib diisi.',
            'selectivity_level.required' => 'Tingkat selektivitas wajib dipilih.',
            'status.required'            => 'Status rekomendasi wajib dipilih.',
        ]);

        $fishingGear->update($validated);

        return redirect()->route('dashboard', ['#fishing-gears-section'])
            ->with('success', "Data alat tangkap {$fishingGear->name} berhasil diperbarui!");
    }

    /**
     * Hapus data alat tangkap.
     */
    public function destroy(FishingGear $fishingGear): RedirectResponse
    {
        $name = $fishingGear->name;
        $fishingGear->delete();

        return redirect()->route('dashboard', ['#fishing-gears-section'])
            ->with('success', "Alat tangkap {$name} berhasil dihapus!");
    }
}
