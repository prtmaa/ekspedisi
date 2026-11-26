<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPenegeluaran extends Model
{
    use HasFactory;

    protected $table = 'jenis_pengeluarans';
    protected $guarded = [];

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'jenis_pengeluaran_id');
    }
}
