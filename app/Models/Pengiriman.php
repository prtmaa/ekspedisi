<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    protected $guarded = [];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function tipe()
    {
        return $this->belongsTo(Tipe::class, 'tipe_pengiriman');
    }

    public function tujuann()
    {
        return $this->belongsTo(Tujuan::class, 'tujuan');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class);
    }
}
