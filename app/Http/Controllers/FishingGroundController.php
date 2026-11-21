<?php

namespace App\Http\Controllers;

use App\Models\FishingGround;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FishingGroundController extends Controller
{
    /**
     * Simpan titik fishing ground baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'latitude'          => ['required', 'numeric', 'between:-90,90'],
            'longitude'         => ['required', 'numeric', 'between:-180,180'],
            'coordinates_label' => ['nullable', 'string', 'max:100'],
            'depth_meters'      => ['nullable', 'string', 'max:50'],
            'recommended_gear'  => ['nullable', 'string', 'max:255'],
            'status'            => ['required', 'string', 'in:Aktif,Musiman,Dibatasi'],
            'description'       => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required'      => 'Nama Fishing Ground wajib diisi.',
            'latitude.required'  => 'Titik Latitude koordinat wajib diisi.',
            'latitude.numeric'   => 'Latitude harus berupa angka desimal.',
            'longitude.required' => 'Titik Longitude koordinat wajib diisi.',
            'longitude.numeric'  => 'Longitude harus berupa angka desimal.',
            'status.required'    => 'Status lokasi wajib dipilih.',
        ]);

        // Auto-generate coordinates_label jika tidak diisi
        if (empty($validated['coordinates_label'])) {
            $latDir = $validated['latitude'] < 0 ? 'S' : 'N';
            $lonDir = $validated['longitude'] < 0 ? 'W' : 'E';
            $validated['coordinates_label'] = abs(round($validated['latitude'], 4)) . "°{$latDir}, " . abs(round($validated['longitude'], 4)) . "°{$lonDir}";
        }

        FishingGround::create($validated);

        return redirect()->route('dashboard', ['#fishing-grounds-section'])
            ->with('success', 'Titik lokasi Fishing Ground berhasil ditambahkan ke peta!');
    }

    /**
     * Perbarui data titik fishing ground.
     */
    public function update(Request $request, FishingGround $fishingGround): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'latitude'          => ['required', 'numeric', 'between:-90,90'],
            'longitude'         => ['required', 'numeric', 'between:-180,180'],
            'coordinates_label' => ['nullable', 'string', 'max:100'],
            'depth_meters'      => ['nullable', 'string', 'max:50'],
            'recommended_gear'  => ['nullable', 'string', 'max:255'],
            'status'            => ['required', 'string', 'in:Aktif,Musiman,Dibatasi'],
            'description'       => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required'      => 'Nama Fishing Ground wajib diisi.',
            'latitude.required'  => 'Titik Latitude koordinat wajib diisi.',
            'longitude.required' => 'Titik Longitude koordinat wajib diisi.',
            'status.required'    => 'Status lokasi wajib dipilih.',
        ]);

        if (empty($validated['coordinates_label'])) {
            $latDir = $validated['latitude'] < 0 ? 'S' : 'N';
            $lonDir = $validated['longitude'] < 0 ? 'W' : 'E';
            $validated['coordinates_label'] = abs(round($validated['latitude'], 4)) . "°{$latDir}, " . abs(round($validated['longitude'], 4)) . "°{$lonDir}";
        }

        $fishingGround->update($validated);

        return redirect()->route('dashboard', ['#fishing-grounds-section'])
            ->with('success', "Data {$fishingGround->name} berhasil diperbarui!");
    }

    /**
     * Hapus titik fishing ground.
     */
    public function destroy(FishingGround $fishingGround): RedirectResponse
    {
        $name = $fishingGround->name;
        $fishingGround->delete();

        return redirect()->route('dashboard', ['#fishing-grounds-section'])
            ->with('success', "Lokasi {$name} berhasil dihapus dari sistem dan peta!");
    }
}
