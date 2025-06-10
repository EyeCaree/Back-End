<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klasifikasi;
use App\Models\FotoMata;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PredictionController extends Controller
{
    public function predict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Simpan gambar ke storage
        $path = $request->file('image')->store('uploads', 'public');
        $fullPath = storage_path('app/public/' . $path);
        $publicPath = 'storage/' . $path;

        // Simpan ke database FotoMata
        $foto = FotoMata::create([
            'user_id' => 1, // Ganti jika sudah login
            'file_path' => $publicPath,
            'upload_date' => now(),
        ]);

        // Jalankan Python predict
        $scriptPath = base_path('app/Http/ML/predict.py');
        $command = escapeshellcmd("python {$scriptPath} {$fullPath}") . " 2>&1";
        $output = shell_exec($command);
        Log::info("Python raw output:", ['output' => $output]);

        $result = json_decode($output, true);

        if (!is_array($result) || !isset($result['class_index'])) {
            Log::error("Gagal membaca hasil prediksi dari Python:", ['output' => $output]);
            return response()->json([
                'error' => 'Prediksi gagal dijalankan. Periksa script Python atau model.'
            ], 500);
        }

        // Label sesuai urutan training
        $classes = ['Bulging_Eyes', 'Cataracts', 'Crossed_Eyes', 'Uveitis'];
        $probabilities = $result['probabilities'] ?? [];

        if ($result['class_index'] === -1) {
            $predicted = 'Normal';
        } else {
            $predicted = $classes[$result['class_index']] ?? 'Tidak dikenali';
        }

        $accuracy = $result['accuracy'] ?? null;

        // Format confidence
        $confidenceFormatted = [];
        foreach ($classes as $index => $label) {
            $confidenceFormatted[$label] = isset($probabilities[$index])
                ? ($label === $predicted ? $probabilities[$index] * 100 . '% (Predicted)' : $probabilities[$index] * 100 . '%')
                : 'N/A';
        }

        // Simpan klasifikasi
        $klasifikasi = Klasifikasi::create([
            'foto_id' => $foto->id,
            'hasil' => $predicted,
            'akurasi' => $accuracy
        ]);

        // Response
        return response()->json([
            'message' => 'Prediksi berhasil',
            'result' => $predicted,
            'akurasi' => $accuracy,
            'foto_id' => $foto->id,
            'confidences' => $confidenceFormatted,
            'data' => $klasifikasi
        ]);
    }
}