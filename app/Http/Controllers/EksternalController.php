<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\EksternalExport;
use Carbon\Carbon;

class EksternalController extends Controller
{
    public function index()
    {
        return view('eksternal.index');
    }

    public function data()
    {
        $pengiriman = Pengiriman::with('driver', 'tipe', 'tujuann')
            ->whereHas('tipe', function ($q) {
                $q->where('nama', 'like', '%sewa eksternal%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return datatables()
            ->of($pengiriman)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($pengiriman) {
                return '<a href="' . route('pengeluaran.index', $pengiriman->id) . '">' . formatTanggalIndo($pengiriman->tanggal) . '</a>';
            })
            ->addColumn('tipe_pengiriman', function ($pengiriman) {
                return $pengiriman->tipe->nama ?? '-';
            })
            ->addColumn('tujuan', function ($pengiriman) {
                return $pengiriman->tujuann->nama ?? '-';
            })
            ->addColumn('pengeluaran', function ($row) {
                $totalPengeluaran = $row->pengeluaran()->sum('nominal');
                return formatRupiah($totalPengeluaran);
            })
            ->rawColumns(['tanggal'])
            ->make(true);
    }

       public function exportBulanan(Request $request)
    {
        $awal  = Carbon::createFromFormat('Y-m', $request->bulan_awal);
        $akhir = Carbon::createFromFormat('Y-m', $request->bulan_akhir);

        return Excel::download(
            new EksternalExport(
                $awal->startOfMonth(),
                $akhir->endOfMonth()
            ),
            'pengiriman_eksternal_' .
                $awal->format('Y_m') .
                '_sd_' .
                $akhir->format('Y_m') .
                '.xlsx'
        );
    }
}
