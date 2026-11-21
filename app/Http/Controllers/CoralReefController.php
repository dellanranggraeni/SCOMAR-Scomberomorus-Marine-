<?php

namespace App\Http\Controllers;

use App\Models\CoralReef;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CoralReefController extends Controller
{
    /**
     * Simpan zona terumbu karang baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'latitude'         => ['required', 'numeric', 'between:-90,90'],
            'longitude'        => ['required', 'numeric', 'between:-180,180'],
            'condition_status' => ['required', 'string', 'in:Sangat Baik,Baik,Sedang,Rusak'],
            'coverage_percent' => ['nullable', 'numeric', 'between:0,100'],
            'friendly_gear'    => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required'             => 'Nama zona terumbu karang wajib diisi.',
            'latitude.required'         => 'Latitude koordinat wajib diisi.',
            'longitude.required'        => 'Longitude koordinat wajib diisi.',
            'condition_status.required' => 'Status kondisi terumbu karang wajib dipilih.',
        ]);

        CoralReef::create($validated);

        return redirect()->route('dashboard', ['#coral-reefs-section'])
            ->with('success', 'Data zona terumbu karang berhasil ditambahkan ke pemantauan!');
    }

    /**
     * Perbarui data zona terumbu karang.
     */
    public function update(Request $request, CoralReef $coralReef): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'latitude'         => ['required', 'numeric', 'between:-90,90'],
            'longitude'        => ['required', 'numeric', 'between:-180,180'],
            'condition_status' => ['required', 'string', 'in:Sangat Baik,Baik,Sedang,Rusak'],
            'coverage_percent' => ['nullable', 'numeric', 'between:0,100'],
            'friendly_gear'    => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required'             => 'Nama zona terumbu karang wajib diisi.',
            'latitude.required'         => 'Latitude koordinat wajib diisi.',
            'longitude.required'        => 'Longitude koordinat wajib diisi.',
            'condition_status.required' => 'Status kondisi terumbu karang wajib dipilih.',
        ]);

        $coralReef->update($validated);

        return redirect()->route('dashboard', ['#coral-reefs-section'])
            ->with('success', "Data {$coralReef->name} berhasil diperbarui!");
    }

    /**
     * Hapus data zona terumbu karang.
     */
    public function destroy(CoralReef $coralReef): RedirectResponse
    {
        $name = $coralReef->name;
        $coralReef->delete();

        return redirect()->route('dashboard', ['#coral-reefs-section'])
            ->with('success', "Zona terumbu karang {$name} berhasil dihapus!");
    }
}
