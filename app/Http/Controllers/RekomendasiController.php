<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rekomendasi;
use Illuminate\Support\Facades\Validator;

class RekomendasiController extends Controller
{
    // Menampilkan semua rekomendasi
    public function index()
    {
        $rekomendasi = Rekomendasi::all();
        return response()->json($rekomendasi);
    }

    // Menampilkan rekomendasi berdasarkan ID
    public function show($id)
    {
        $rekomendasi = Rekomendasi::find($id);
        if (!$rekomendasi) {
            return response()->json(['message' => 'Rekomendasi tidak ditemukan'], 404);
        }
        return response()->json([
            'solusi' => $rekomendasi->solusi
        ]);
    }

    // Menambahkan rekomendasi baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'solusi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $rekomendasi = Rekomendasi::create($request->all());

        return response()->json([
            'message' => 'Rekomendasi berhasil disimpan!',
            'data' => $rekomendasi
        ]);
    }

    // Memperbarui rekomendasi
    public function update(Request $request, $id)
    {
        $rekomendasi = Rekomendasi::find($id);
        if (!$rekomendasi) {
            return response()->json(['message' => 'Rekomendasi tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'solusi' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rekomendasi->update($request->all());
        return response()->json($rekomendasi);
    }

    // Menghapus rekomendasi
    public function destroy($id)
    {
        $rekomendasi = Rekomendasi::find($id);
        if (!$rekomendasi) {
            return response()->json(['message' => 'Rekomendasi tidak ditemukan'], 404);
        }

        $rekomendasi->delete();
        return response()->json(['message' => 'Rekomendasi berhasil dihapus']);
    }
}