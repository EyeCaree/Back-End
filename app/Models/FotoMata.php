<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Klasifikasi;

class FotoMata extends Model
{
    use HasFactory;

    // Pastikan nama tabel sesuai jika tidak sesuai konvensi Laravel
    protected $table = 'foto_mata';

    // Izinkan mass assignment untuk kolom berikut
    protected $fillable = ['user_id', 'file_path', 'upload_date'];

    // Nonaktifkan timestamps jika tidak ada di tabel
    public $timestamps = false;

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan Klasifikasi
    public function klasifikasi()
    {
        return $this->hasOne(Klasifikasi::class, 'foto_id');
    }
}