<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekaptipeController extends Controller
{
    public function index()
    {
        return view('rekaptipe.index');
    }


    public function tipebulan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // Subquery: total sewa per tipe (langsung dari tabel pengiriman)
        $subSewa = DB::table('pengiriman')
            ->select('tipe_pengiriman', DB::raw('SUM(sewa) as total_sewa'))
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->groupBy('tipe_pengiriman');

        // Subquery: total pengeluaran per tipe
        $subPengeluaran = DB::table('pengiriman')
            ->join('pengeluaran', 'pengiriman.id', '=', 'pengeluaran.pengiriman_id')
            ->select('pengiriman.tipe_pengiriman', DB::raw('SUM(pengeluaran.nominal) as total_pengeluaran'))
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('pengiriman.tanggal', [$startDate, $endDate]);
            })
            ->groupBy('pengiriman.tipe_pengiriman');

        // Gabungkan ke tabel tipe
        $data = DB::table('tipe')
            ->leftJoinSub($subSewa, 's', function ($join) {
                $join->on('tipe.id', '=', 's.tipe_pengiriman');
            })
            ->leftJoinSub($subPengeluaran, 'p', function ($join) {
                $join->on('tipe.id', '=', 'p.tipe_pengiriman');
            })
            ->select(
                'tipe.nama as tipe',
                DB::raw('COALESCE(s.total_sewa, 0) as sewa'),
                DB::raw('COALESCE(p.total_pengeluaran, 0) as pengeluaran'),
                DB::raw('(COALESCE(s.total_sewa, 0) - COALESCE(p.total_pengeluaran, 0)) as pendapatan')
            )
            ->get();

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->addColumn('sewa_rupiah', fn($row) => 'Rp ' . number_format($row->sewa, 0, ',', '.'))
            ->addColumn('pengeluaran_rupiah', fn($row) => 'Rp ' . number_format($row->pengeluaran, 0, ',', '.'))
            ->addColumn('pendapatan_rupiah', fn($row) => 'Rp ' . number_format($row->pendapatan, 0, ',', '.'))
            ->make(true);
    }

    public function chart(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $query = DB::table('pengiriman')
            ->leftJoin('pengeluaran', 'pengiriman.id', '=', 'pengeluaran.pengiriman_id')
            ->select(
                DB::raw("DATE_FORMAT(pengiriman.tanggal, '%Y-%m') as bulan"),
                'pengiriman.tipe_pengiriman',
                DB::raw('SUM(pengiriman.sewa) as total_sewa'),
                DB::raw('SUM(pengeluaran.nominal) as total_pengeluaran')
            )
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('pengiriman.tanggal', [$startDate, $endDate])
            )
            ->groupBy('bulan', 'pengiriman.tipe_pengiriman')
            ->orderBy('bulan')
            ->get();

        // === REFORMAT DATA UNTUK CHART ===
        $bulanList = [];
        $tipeList = DB::table('tipe')->pluck('nama', 'id')->toArray();

        $result = [];
        foreach ($query as $row) {
            $bulan = $row->bulan;
            $tipe  = $tipeList[$row->tipe_pengiriman] ?? 'Tidak diketahui';

            $pendapatan = ($row->total_sewa ?? 0) - ($row->total_pengeluaran ?? 0);

            $result[$tipe][$bulan] = $pendapatan;

            if (!in_array($bulan, $bulanList)) {
                $bulanList[] = $bulan;
            }
        }

        sort($bulanList);

        return response()->json([
            'bulan' => $bulanList,
            'data'  => $result
        ]);
    }



    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // === Subquery total sewa per tipe (tanpa join tujuan) ===
        $subSewa = DB::table('pengiriman')
            ->select('tipe_pengiriman', DB::raw('SUM(sewa) as total_sewa'))
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('tanggal', [$startDate, $endDate])
            )
            ->groupBy('tipe_pengiriman');

        // === Subquery total pengeluaran per tipe ===
        $subPengeluaran = DB::table('pengiriman')
            ->join('pengeluaran', 'pengiriman.id', '=', 'pengeluaran.pengiriman_id')
            ->select('pengiriman.tipe_pengiriman', DB::raw('SUM(pengeluaran.nominal) as total_pengeluaran'))
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('pengiriman.tanggal', [$startDate, $endDate])
            )
            ->groupBy('pengiriman.tipe_pengiriman');

        // === Rekap final ===
        $data = DB::table('tipe')
            ->leftJoinSub(
                $subSewa,
                's',
                fn($join) =>
                $join->on('tipe.id', '=', 's.tipe_pengiriman')
            )
            ->leftJoinSub(
                $subPengeluaran,
                'p',
                fn($join) =>
                $join->on('tipe.id', '=', 'p.tipe_pengiriman')
            )
            ->select(
                'tipe.nama as tipe',
                DB::raw('COALESCE(s.total_sewa, 0) as sewa'),
                DB::raw('COALESCE(p.total_pengeluaran, 0) as pengeluaran'),
                DB::raw('(COALESCE(s.total_sewa, 0) - COALESCE(p.total_pengeluaran, 0)) as pendapatan')
            )
            ->get();

        // === Load template Excel ===
        $templatePath = storage_path('app/template/template3.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Start row template
        $row = 12;
        $no = 1;

        $totalSewa = 0;
        $totalPengeluaran = 0;
        $totalPendapatan = 0;

        foreach ($data as $item) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $item->tipe);

            $sheet->setCellValue("C{$row}", (float)$item->sewa);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('"Rp." #,##0');

            $sheet->setCellValue("D{$row}", (float)$item->pengeluaran);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp." #,##0');

            $sheet->setCellValue("E{$row}", (float)$item->pendapatan);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('"Rp." #,##0');

            $totalSewa        += (float)$item->sewa;
            $totalPengeluaran += (float)$item->pengeluaran;
            $totalPendapatan  += (float)$item->pendapatan;

            $row++;
        }

        // === TOTAL di baris 27 ===
        $sheet->setCellValue("B27", "TOTAL");

        $sheet->setCellValue("C27", $totalSewa);
        $sheet->getStyle("C27")->getNumberFormat()->setFormatCode('"Rp." #,##0');

        $sheet->setCellValue("D27", $totalPengeluaran);
        $sheet->getStyle("D27")->getNumberFormat()->setFormatCode('"Rp." #,##0');

        $sheet->setCellValue("E27", $totalPendapatan);
        $sheet->getStyle("E27")->getNumberFormat()->setFormatCode('"Rp." #,##0');

        $sheet->getStyle("B27:E27")->getFont()->setBold(true);

        // Judul Periode
        $sheet->setCellValue(
            "A8",
            "Rekap Pengeluaran Biaya Ekspedisi Per Tipe Periode: {$startDate} s/d {$endDate}"
        );

        // Output
        $fileName = 'Rekap_Tipe_' . ($startDate ?? '') . '_sd_' . ($endDate ?? '') . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }
}
