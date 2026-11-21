<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class ScannerController extends Controller
{
    /**
     * Menampilkan halaman pemindai ikan.
     */
    public function index()
    {
        return view('scanner.index', [
            'scanResult' => session('scan_result'),
            'validationResult' => session('validation_result'),
        ]);
    }

    /**
     * Mengirim tiga foto ke FastAPI untuk divalidasi
     * dan diprediksi oleh model AI SCOMAR.
     */
    public function predict(Request $request)
    {
        // Validasi foto yang diunggah
        $request->validate([
            'genus_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'mata_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'insang_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        try {
            $genus = $request->file('genus_image');
            $mata = $request->file('mata_image');
            $insang = $request->file('insang_image');

            // Mengirim tiga foto ke FastAPI
            $response = Http::timeout(180)
                ->acceptJson()

                ->attach(
                    'genus_image',
                    file_get_contents($genus->getRealPath()),
                    $genus->getClientOriginalName()
                )

                ->attach(
                    'mata_image',
                    file_get_contents($mata->getRealPath()),
                    $mata->getClientOriginalName()
                )

                ->attach(
                    'insang_image',
                    file_get_contents($insang->getRealPath()),
                    $insang->getClientOriginalName()
                )

                ->post('http://127.0.0.1:8001/predict/pemindaian');

            // ======================================
            // 1. PENANGANAN GAMBAR TIDAK SESUAI
            // ======================================

            if ($response->status() === 422) {

                $detail = $response->json('detail', []);

                if (!is_array($detail)) {
                    $detail = [
                        'pesan' => 'Gambar tidak sesuai dengan ketentuan pemindaian.'
                    ];
                }

                $pesan = $detail['pesan']
                    ?? 'Satu atau lebih gambar tidak sesuai. Silakan unggah ulang.';

                return back()
                    ->withErrors([
                        'scanner' => $pesan
                    ])
                    ->with('validation_result', $detail)
                    ->with('scan_result', null);
            }

            // ======================================
            // 2. PENANGANAN KEGAGALAN AI SERVICE
            // ======================================

            if (!$response->successful()) {

                return back()
                    ->withErrors([
                        'scanner' => 'Pemindaian AI gagal. Status FastAPI: '
                            . $response->status()
                    ])
                    ->with('validation_result', null);
            }

            // ======================================
            // 3. PEMINDAIAN BERHASIL
            // ======================================

            $hasil = $response->json();

            return redirect()
                ->route('scanner.index')
                ->with('scan_result', $hasil)
                ->with('validation_result', null);

        } catch (ConnectionException $e) {

            return back()
                ->withErrors([
                    'scanner' => 'Tidak dapat terhubung ke AI Service. Pastikan FastAPI berjalan di port 8001.'
                ])
                ->with('validation_result', null);

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withErrors([
                    'scanner' => 'Terjadi kesalahan saat memproses gambar.'
                ])
                ->with('validation_result', null);
        }
    }
}