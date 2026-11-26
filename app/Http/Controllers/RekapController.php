<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class RekapController extends Controller
{
    public function index()
    {
        return view('rekap.index');
    }

    public function data(Request $request)
    {
        $bulan = $request->input('bulan'); // format: 2025-05

        $data = DB::table('pengeluaran')
            ->join('pengiriman', 'pengeluaran.pengiriman_id', '=', 'pengiriman.id')
            ->join('jenis_pengeluarans', 'pengeluaran.jenis_pengeluaran_id', '=', 'jenis_pengeluarans.id')
            ->select(
                'jenis_pengeluarans.nama as jenis_pengeluaran',
                DB::raw("SUM(pengeluaran.nominal) as total")
            )
            ->whereRaw('LOWER(jenis_pengeluarans.role) = ?', ['warehouse'])
            ->when($bulan, function ($q) use ($bulan) {
                $q->whereRaw("DATE_FORMAT(pengiriman.tanggal, '%Y-%m') = ?", [$bulan]);
            })
            ->groupBy('jenis_pengeluarans.nama')
            ->get();



        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->addColumn('total_rupiah', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->make(true);
    }

    // public function data(Request $request)
    // {
    //     $bulan = $request->input('bulan'); // format: 2025-05

    //     $data = DB::table('pengeluaran')
    //         ->join('pengiriman', 'pengeluaran.pengiriman_id', '=', 'pengiriman.id')
    //         ->join('jenis_pengeluarans', 'pengeluaran.jenis_pengeluaran_id', '=', 'jenis_pengeluarans.id')
    //         ->select(
    //             'jenis_pengeluarans.nama as jenis_pengeluaran',
    //             DB::raw("SUM(pengeluaran.nominal) as total")
    //         )
    //         ->when($bulan, function ($q) use ($bulan) {
    //             $q->whereRaw("DATE_FORMAT(pengiriman.tanggal, '%Y-%m') = ?", [$bulan]);
    //         })
    //         ->groupBy('jenis_pengeluarans.nama')
    //         ->get();

    //     return datatables()
    //         ->of($data)
    //         ->addIndexColumn()
    //         ->addColumn('total_rupiah', function ($row) {
    //             return 'Rp ' . number_format($row->total, 0, ',', '.');
    //         })
    //         ->make(true);
    // }


    public function chartData(Request $request)
    {
        try {
            $tahun = $request->tahun ?? date('Y');

            $data = DB::table('pengeluaran')
                ->join('pengiriman', 'pengeluaran.pengiriman_id', '=', 'pengiriman.id')
                ->join('jenis_pengeluarans', 'pengeluaran.jenis_pengeluaran_id', '=', 'jenis_pengeluarans.id')
                ->whereRaw('LOWER(jenis_pengeluarans.role) = ?', ['warehouse'])
                ->whereYear('pengiriman.tanggal', $tahun)
                ->select(
                    DB::raw("MONTH(pengiriman.tanggal) as bulan"),
                    'jenis_pengeluarans.nama as jenis',
                    DB::raw("SUM(pengeluaran.nominal) as total")
                )
                ->groupBy(
                    DB::raw("MONTH(pengiriman.tanggal)"),
                    'jenis_pengeluarans.nama'
                )
                ->orderBy(DB::raw("MONTH(pengiriman.tanggal)"))
                ->get();

            return response()->json([
                'tahun' => $tahun,
                'data'  => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




    public function detailJenis(Request $request)
    {
        $bulan = $request->input('bulan');
        $jenis = $request->input('jenis');

        $data = DB::table('pengeluaran')
            ->join('pengiriman', 'pengeluaran.pengiriman_id', '=', 'pengiriman.id')
            ->join('jenis_pengeluarans', 'pengeluaran.jenis_pengeluaran_id', '=', 'jenis_pengeluarans.id')
            ->where('jenis_pengeluarans.nama', $jenis)
            ->whereRaw("DATE_FORMAT(pengiriman.tanggal, '%Y-%m') = ?", [$bulan])
            ->select(
                'pengeluaran.nominal',
                'pengeluaran.keterangan',
                'pengiriman.tanggal'
            )
            ->orderBy('pengiriman.tanggal', 'asc')
            ->get();

        // Format tanggal
        $data->transform(function ($item) {
            $item->tanggal = \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y');
            return $item;
        });

        return response()->json($data);
    }


    public function export(Request $request)
    {
        $bulan = $request->input('bulan');   // ex: 2025-05 (YYYY-MM)

        /* === 1. Ambil data === */
        $pengeluaran = DB::table('pengeluaran')
            ->join('pengiriman', 'pengeluaran.pengiriman_id', '=', 'pengiriman.id')
            ->join('jenis_pengeluarans', 'pengeluaran.jenis_pengeluaran_id', '=', 'jenis_pengeluarans.id')
            ->whereRaw('LOWER(jenis_pengeluarans.role) = ?', ['warehouse'])
            ->select(
                'jenis_pengeluarans.nama as jenis_pengeluaran',
                DB::raw('SUM(pengeluaran.nominal) AS total')
            )
            ->when($bulan, function ($q) use ($bulan) {
                $q->whereRaw("DATE_FORMAT(pengiriman.tanggal, '%Y-%m') = ?", [$bulan]);
            })
            ->groupBy(
                'pengeluaran.jenis_pengeluaran_id',
                'jenis_pengeluarans.nama'
            )
            ->get();


        /* === 2. Load template === */
        $template = storage_path('app/template/template2.xlsx');
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getActiveSheet();

        if ($bulan) {
            $bulanFormatted = Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
        } else {
            $bulanFormatted = 'Semua';
        }

        /* === 3. Tulis judul === */
        $sheet->setCellValue('A8', 'Rekap Total Pengeluaran Biaya Ekspedisi Bulan ' . $bulanFormatted);

        $totalSemua = $pengeluaran->sum('total');

        /* === 4. Tulis data mulai baris 12 (B-C) === */
        $startRow = 12;
        $maxRow   = 46;
        $no = 1;

        foreach ($pengeluaran as $index => $row) {
            $currentRow = $startRow + $index;
            if ($currentRow > $maxRow) break;

            $sheet->setCellValue("A{$currentRow}", $no++);
            $sheet->setCellValue("B{$currentRow}", $row->jenis_pengeluaran);  // <-- sudah nama dari tabel FK
            $sheet->setCellValue(
                "C{$currentRow}",
                'Rp. ' . number_format($row->total, 0, ',', '.')
            );
        }

        $sheet->setCellValue('F36', 'Rp. ' . number_format($totalSemua, 0, ',', '.'));
        $sheet->getStyle('F36')->getFont()->setBold(true);

        /* === 5. Download === */
        $filename = 'Laporan_Pengeluaran_' . ($bulan ?: now()->format('Ym')) . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'pengeluaran_');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
