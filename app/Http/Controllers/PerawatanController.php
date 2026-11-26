<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class PerawatanController extends Controller
{
    public function index()
    {
        return view('perawatan.index');
    }

    public function data()
    {
        $pengeluaran = Pengeluaran::with(['pengiriman.tipe'])
            ->where('jenis_pengeluaran_id', '6')
            ->orderBy('created_at', 'desc')
            ->get();

        return datatables()
            ->of($pengeluaran)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($row) {
                return formatTanggalIndo($row->pengiriman->tanggal) ?? '-';
            })
            ->addColumn('tipe', function ($row) {
                return $row->pengiriman->tipe->nama ?? '-';
            })
            ->addColumn('nominal', function ($row) {
                return formatRupiah($row->nominal);
            })
            ->make(true);
    }
}
