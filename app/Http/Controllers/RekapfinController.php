<?php

namespace App\Http\Controllers;

use App\Models\Pajak;
use App\Models\Tujuan;
use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapfinController extends Controller
{
    public function index()
    {
        $daftarTujuan = Tujuan::orderBy('nama')->get();
        $defaultTujuan = [1, 2, 3, 5, 6]; // default terpilih

        return view('finance.rekap.index', compact('daftarTujuan', 'defaultTujuan'));
    }

    public function data(Request $request)
    {
        $bulan  = $request->bulan;
        $tujuan = $request->tujuan ?? [];

        $data = DB::table('tujuan')
            ->leftJoin('pengiriman', function ($join) use ($bulan) {
                $join->on('tujuan.id', '=', 'pengiriman.tujuan')
                    ->when($bulan, function ($q) use ($bulan) {
                        $q->whereRaw("DATE_FORMAT(pengiriman.tanggal, '%Y-%m') = ?", [$bulan]);
                    });
            })
            ->select(
                'tujuan.id',
                'tujuan.nama as tujuan',
                DB::raw('COALESCE(SUM(pengiriman.sewa), 0) as total')
            )
            ->when(!empty($tujuan), function ($q) use ($tujuan) {
                $q->whereIn('tujuan.id', $tujuan);
            })
            ->groupBy('tujuan.id', 'tujuan.nama')
            ->orderBy('tujuan.nama')
            ->get();

        // TOTAL KESELURUHAN
        $grandTotal = $data->sum('total');

        // HITUNG PERSEN
        $data = $data->map(function ($row) use ($grandTotal) {
            $row->persen = $grandTotal > 0
                ? round(($row->total / $grandTotal) * 100, 2)
                : 0;
            return $row;
        });

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->addColumn(
                'total_rupiah',
                fn($row) =>
                'Rp ' . number_format($row->total, 0, ',', '.')
            )
            ->addColumn(
                'persen_view',
                fn($row) =>
                $row->persen . ' %'
            )
            ->make(true);
    }

    public function dataJenis(Request $request)
    {
        $bulan = $request->bulan;

        // DATA PENGELUARAN PER JENIS
        $data = DB::table('jenis_pengeluarans')
            ->leftJoin('pengeluaran', 'jenis_pengeluarans.id', '=', 'pengeluaran.jenis_pengeluaran_id')
            ->leftJoin('pengiriman', 'pengeluaran.pengiriman_id', '=', 'pengiriman.id')
            ->select(
                'jenis_pengeluarans.id',
                'jenis_pengeluarans.nama as jenis_pengeluaran',
                DB::raw("
                COALESCE(SUM(
                    CASE 
                        WHEN DATE_FORMAT(pengiriman.tanggal, '%Y-%m') = '{$bulan}'
                        THEN pengeluaran.nominal
                        ELSE 0
                    END
                ), 0) as total
            ")
            )
            ->groupBy('jenis_pengeluarans.id', 'jenis_pengeluarans.nama')
            ->orderBy('jenis_pengeluarans.nama')
            ->get();

        // TOTAL TUJUAN (untuk hitung PPN)
        $totalTujuan = DB::table('pengiriman')
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = '{$bulan}'")
            ->sum('sewa');

        // 🔹 PPN dihitung % dari TOTAL TUJUAN
        $ppn = Pajak::where('nama', 'Pencadangan Biaya PPN')->first();
        if ($ppn) {
            $nominalPPN = round($totalTujuan * ($ppn->persen / 100));
            $data->push((object)[
                'id' => 'ppn_' . $ppn->id,
                'jenis_pengeluaran' => $ppn->nama . ' (' . $ppn->persen . '%)',
                'total' => $nominalPPN
            ]);
        }

        // 🔹 SUSUT → nominal FIX dari DB, bukan persen
        $susut = Pajak::where('nama', 'Biaya Depresiasi Aset')->first();
        if ($susut) {
            $data->push((object)[
                'id' => 'susut_' . $susut->id,
                'jenis_pengeluaran' => $susut->nama . ' (2.00%)',
                'total' => $susut->persen // langsung nilai
            ]);
        }

        // GRAND TOTAL PENGELUARAN BARU
        $grandTotalBaru = $data->sum('total');

        // HITUNG PERSEN
        $data = $data->map(function ($row) use ($grandTotalBaru) {
            $row->persen = $grandTotalBaru > 0
                ? round(($row->total / $grandTotalBaru) * 100, 2)
                : 0;
            return $row;
        });

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->addColumn('total_rupiah', fn($row) => 'Rp ' . number_format($row->total, 0, ',', '.'))
            ->addColumn('persen_view', fn($row) => $row->persen . ' %')
            ->make(true);
    }




    public function dataLaba(Request $request)
    {

        $bulan = $request->bulan;

        // Total Pendapatan
        $pendapatan = DB::table('pengiriman')
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->sum('sewa');

        // Total Pengeluaran (tanpa pajak tambahan)
        $pengeluaran = DB::table('pengeluaran')
            ->join('pengiriman', 'pengeluaran.pengiriman_id', '=', 'pengiriman.id')
            ->whereRaw("DATE_FORMAT(pengiriman.tanggal, '%Y-%m') = ?", [$bulan])
            ->sum('pengeluaran.nominal');

        // PPN (% dari pendapatan)
        $ppn = Pajak::where('nama', 'Pencadangan Biaya PPN')->first();
        $nominalPPN = $ppn ? round($pendapatan * ($ppn->persen / 100)) : 0;

        // SUSUT (nilai fix, langsung dari database)
        $susut = Pajak::where('nama', 'Biaya Depresiasi Aset')->first();
        $nominalSusut = $susut ? $susut->persen : 0;

        // Total Pengeluaran Akhir
        $pengeluaranAkhir = $pengeluaran + $nominalPPN + $nominalSusut;

        // Laba
        $laba = $pendapatan - $pengeluaranAkhir;

        return response()->json([
            'total_tujuan' => $pendapatan,
            'total_pengeluaran' => $pengeluaranAkhir,
            'total_laba' => $laba,
        ]);

        dd([
            'pendapatan' => $pendapatan,
            'pengeluaran' => $pengeluaran,
            'ppn' => $nominalPPN,
            'susut' => $nominalSusut
        ]);
    }
}
