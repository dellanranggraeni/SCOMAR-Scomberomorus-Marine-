<?php

namespace App\Http\Controllers;

use App\Models\OceanographicParameter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OceanographicParameterController extends Controller
{
    /**
     * Simpan data parameter oseanografi baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year'          => ['required', 'digits:4'],
            'temp'          => ['required', 'numeric', 'between:20,40'],
            'chlorophyll'   => ['required', 'numeric', 'between:0,10'],
            'tss'           => ['required', 'numeric', 'between:0,100'],
            'current'       => ['required', 'numeric', 'between:0,10'],
            'status'        => ['required', 'string', 'max:100'],
            'habitat_score' => ['required', 'string', 'max:100'],
            'source'        => ['required', 'string', 'max:255'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ], [
            'year.required'          => 'Tahun pengamatan wajib diisi.',
            'temp.required'          => 'Suhu Permukaan Laut (SPL) wajib diisi.',
            'chlorophyll.required'   => 'Konsentrasi Klorofil-a wajib diisi.',
            'tss.required'           => 'Nilai TSS wajib diisi.',
            'current.required'       => 'Kecepatan arus wajib diisi.',
            'status.required'        => 'Status kesuburan perairan wajib diisi.',
            'habitat_score.required' => 'Skor habitat pelagis wajib diisi.',
            'source.required'        => 'Sumber data / validasi wajib diisi.',
        ]);

        OceanographicParameter::create($validated);

        return redirect()->back()
            ->with('success', 'Data parameter oseanografi berhasil ditambahkan!');
    }

    /**
     * Perbarui data parameter oseanografi yang sudah ada.
     */
    public function update(Request $request, OceanographicParameter $parameter): RedirectResponse
    {
        $validated = $request->validate([
            'year'          => ['required', 'digits:4'],
            'temp'          => ['required', 'numeric', 'between:20,40'],
            'chlorophyll'   => ['required', 'numeric', 'between:0,10'],
            'tss'           => ['required', 'numeric', 'between:0,100'],
            'current'       => ['required', 'numeric', 'between:0,10'],
            'status'        => ['required', 'string', 'max:100'],
            'habitat_score' => ['required', 'string', 'max:100'],
            'source'        => ['required', 'string', 'max:255'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ], [
            'year.required'          => 'Tahun pengamatan wajib diisi.',
            'temp.required'          => 'Suhu Permukaan Laut (SPL) wajib diisi.',
            'chlorophyll.required'   => 'Konsentrasi Klorofil-a wajib diisi.',
            'tss.required'           => 'Nilai TSS wajib diisi.',
            'current.required'       => 'Kecepatan arus wajib diisi.',
            'status.required'        => 'Status kesuburan perairan wajib diisi.',
            'habitat_score.required' => 'Skor habitat pelagis wajib diisi.',
            'source.required'        => 'Sumber data / validasi wajib diisi.',
        ]);

        $parameter->update($validated);

        return redirect()->back()
            ->with('success', 'Data parameter oseanografi tahun ' . $parameter->year . ' berhasil diperbarui!');
    }

    /**
     * Hapus data parameter oseanografi.
     */
    public function destroy(OceanographicParameter $parameter): RedirectResponse
    {
        $year = $parameter->year;
        $parameter->delete();

        return redirect()->back()
            ->with('success', "Data parameter oseanografi tahun {$year} berhasil dihapus!");
    }
}
