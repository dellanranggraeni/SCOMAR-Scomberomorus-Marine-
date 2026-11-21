<?php

namespace App\Http\Controllers;

use App\Models\CatchData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CatchDataController extends Controller
{
    /**
     * Simpan data hasil tangkapan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year'        => ['required', 'digits:4'],
            'season'      => ['required', 'string', 'max:50'],
            'period'      => ['required', 'string', 'max:100'],
            'total_catch' => ['required', 'numeric', 'min:0'],
            'total_count' => ['required', 'integer', 'min:0'],
            'trips'       => ['required', 'integer', 'min:1'],
            'cpue'        => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ], [
            'year.required'        => 'Tahun wajib diisi.',
            'year.digits'          => 'Format tahun harus 4 digit angka.',
            'season.required'      => 'Musim penangkapan wajib dipilih.',
            'period.required'      => 'Periode bulan wajib diisi.',
            'total_catch.required' => 'Total tangkapan (kg) wajib diisi.',
            'total_catch.numeric'  => 'Total tangkapan harus berupa angka.',
            'total_count.required' => 'Jumlah ekor wajib diisi.',
            'trips.required'       => 'Jumlah trip wajib diisi minimal 1.',
        ]);

        if (empty($validated['cpue']) && $validated['trips'] > 0) {
            $validated['cpue'] = round($validated['total_catch'] / $validated['trips'], 2);
        }

        CatchData::create($validated);

        return redirect()->back()
            ->with('success', 'Data hasil tangkapan berhasil ditambahkan!');
    }

    /**
     * Perbarui data hasil tangkapan yang sudah ada.
     */
    public function update(Request $request, CatchData $catch): RedirectResponse
    {
        $validated = $request->validate([
            'year'        => ['required', 'digits:4'],
            'season'      => ['required', 'string', 'max:50'],
            'period'      => ['required', 'string', 'max:100'],
            'total_catch' => ['required', 'numeric', 'min:0'],
            'total_count' => ['required', 'integer', 'min:0'],
            'trips'       => ['required', 'integer', 'min:1'],
            'cpue'        => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ], [
            'year.required'        => 'Tahun wajib diisi.',
            'season.required'      => 'Musim penangkapan wajib dipilih.',
            'period.required'      => 'Periode bulan wajib diisi.',
            'total_catch.required' => 'Total tangkapan (kg) wajib diisi.',
            'total_count.required' => 'Jumlah ekor wajib diisi.',
            'trips.required'       => 'Jumlah trip wajib diisi minimal 1.',
        ]);

        if (empty($validated['cpue']) && $validated['trips'] > 0) {
            $validated['cpue'] = round($validated['total_catch'] / $validated['trips'], 2);
        }

        $catch->update($validated);

        return redirect()->back()
            ->with('success', 'Data hasil tangkapan berhasil diperbarui!');
    }

    /**
     * Hapus data hasil tangkapan.
     */
    public function destroy(CatchData $catch): RedirectResponse
    {
        $info = "{$catch->season} {$catch->year}";
        $catch->delete();

        return redirect()->back()
            ->with('success', "Data hasil tangkapan ({$info}) berhasil dihapus!");
    }
}
