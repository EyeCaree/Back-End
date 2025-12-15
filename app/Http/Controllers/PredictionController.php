<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\FotoMata;
use App\Models\Klasifikasi;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    public function predict(Request $request)
    {
        // 🔹 1️⃣ Validasi file gambar
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // 🔹 2️⃣ Simpan gambar ke storage
        $path = $request->file('image')->store('uploads', 'public');
        $fullPath = storage_path('app/public/' . $path);
        $publicPath = 'storage/' . $path;

        // Simpan ke tabel foto_mata
        $foto = FotoMata::create([
            'user_id' => Auth::id() ?? 1, // fallback user_id 1 jika belum login
            'file_path' => $publicPath,
            'upload_date' => now(),
        ]);

        try {
            // 🔹 3️⃣ Kirim gambar ke API Flask (port 8080)
            $response = Http::attach(
                'file',
                file_get_contents($fullPath),
                basename($fullPath)
            )->post('https://eyecaremodel.onrender.com/predict');

            if (!$response->ok()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghubungi API Flask',
                    'error' => $response->body()
                ], 500);
            }

            $result = $response->json();

            // 🔹 4️⃣ Ambil hasil dari Flask
            $predicted = $result['prediction'] ?? 'Tidak dikenali';
            $confidence = $result['confidence'] ?? 0;
            $probabilities = $result['probabilities'] ?? [];

            // Daftar kelas sesuai training
            $classes = ['Bulging_Eyes', 'Cataracts', 'Crossed_Eyes', 'Uveitis', 'Normal'];

            // 🔹 Jika deteksi "bukan mata"
            if (strtolower($predicted) === 'bukan mata') {
                $predicted = 'Bukan Mata';
            }

            // 🔹 Simpan ke tabel klasifikasi
            $klasifikasi = Klasifikasi::create([
                'foto_id' => $foto->id,
                'hasil' => $predicted,
                'akurasi' => $confidence,
            ]);

            // 🔹 5️⃣ Format confidence per kelas (jika tersedia)
            $confidenceFormatted = [];
            foreach ($classes as $index => $label) {
                $confidenceFormatted[$label] = isset($probabilities[$index])
                    ? round($probabilities[$index] * 100, 2) . '%'
                    : 'N/A';
            }

            // 🔹 6️⃣ Kirim hasil ke frontend
            return response()->json([
                'success' => true,
                'message' => 'Prediksi berhasil dijalankan',
                'result' => [
                    'kelas' => $predicted,
                    'akurasi' => round($confidence * 100, 2) . '%',
                    'foto_id' => $foto->id,
                    'confidence' => $confidenceFormatted,
                ],
                'data' => $klasifikasi
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal melakukan prediksi: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan prediksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}