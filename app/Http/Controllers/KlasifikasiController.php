<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klasifikasi;
use Illuminate\Support\Facades\Validator;

class KlasifikasiController extends Controller
{
    // Menampilkan semua hasil klasifikasi
    public function index()
    {
        $klasifikasi = Klasifikasi::all();
        return response()->json($klasifikasi);
    }

    // Menampilkan hasil klasifikasi berdasarkan foto_id
    public function show($id)
    {
        $klasifikasi = Klasifikasi::where('foto_id', $id)->first();
        if (!$klasifikasi) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        return response()->json([
            'hasil' => $klasifikasi->hasil,
            'akurasi' => $klasifikasi->akurasi
        ]);
    }

    // Menambahkan hasil klasifikasi baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'foto_id' => 'required|exists:foto_matas,id',
            'hasil' => 'required|string',
            'akurasi' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $klasifikasi = Klasifikasi::create($request->all());

        return response()->json([
            'message' => 'Data klasifikasi berhasil disimpan!',
            'data' => $klasifikasi
        ]);
    }

    // Memperbarui hasil klasifikasi
    public function update(Request $request, $id)
    {
        $klasifikasi = Klasifikasi::where('foto_id', $id)->first();
        if (!$klasifikasi) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'hasil' => 'sometimes|string',
            'akurasi' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $klasifikasi->update($request->all());
        return response()->json($klasifikasi);
    }

    // Menghapus hasil klasifikasi
    public function destroy($id)
    {
        $klasifikasi = Klasifikasi::where('foto_id', $id)->first();
        if (!$klasifikasi) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $klasifikasi->delete();
        return response()->json(['message' => 'Data klasifikasi berhasil dihapus']);
    }
}