<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';
    protected $fillable = ['pengiriman_id', 'jenis_pengeluaran_id', 'jenis_pengeluaran', 'nominal', 'keterangan'];

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class);
    }

    public function jenisPengeluaran()
    {
        return $this->belongsTo(JenisPenegeluaran::class, 'jenis_pengeluaran_id');
    }
}
