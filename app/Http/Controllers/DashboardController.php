<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Pengiriman;
use App\Models\Tipe;
use Illuminate\Http\Request;
use App\Exports\PengirimanTemplateExport;
use App\Models\Tujuan;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index()
    {
        return view('finance.dashboard.index');
    }

    public function data()
    {
        $pengiriman = Pengiriman::with('driver', 'tipe', 'tujuann')
            ->orderBy('created_at', 'desc')
            ->get();

        return datatables()
            ->of($pengiriman)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($pengiriman) {
                return '<a href="' . route('pengeluaranfin.index', $pengiriman->id) . '">' . formatTanggalIndo($pengiriman->tanggal) . '</a>';
            })
            ->addColumn('tipe_pengiriman', function ($pengiriman) {
                return $pengiriman->tipe->nama ?? '-';
            })
            ->addColumn('driver_nama', function ($pengiriman) {
                return $pengiriman->driver->nama ?? '-';
            })
            ->addColumn('tujuan', function ($pengiriman) {
                return $pengiriman->tujuann->nama ?? '-';
            })
            ->addColumn('sewa', function ($row) {
                return formatRupiah($row->sewa);
            })
            ->addColumn('bon_sementara', function ($row) {
                return formatRupiah($row->bon_sementara);
            })
            ->addColumn('pengeluaran', function ($row) {
                $totalPengeluaran = $row->pengeluaran()->sum('nominal');
                return formatRupiah($totalPengeluaran);
            })
            ->addColumn('sisa', function ($row) {
                $totalPengeluaran = $row->pengeluaran()->sum('nominal');
                $sisa = $row->bon_sementara - $totalPengeluaran;
                return formatRupiah($sisa);
            })
            ->rawColumns(['tanggal'])
            ->make(true);
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $pengiriman = new Pengiriman();
        $bs = preg_replace('/\D/', '', $request->bs);
        $sewa = preg_replace('/\D/', '', $request->sewa);


        $pengiriman->tanggal = $request->tanggal;
        $pengiriman->tipe_pengiriman = $request->tipe;
        $pengiriman->tujuan = $request->tujuan;
        $pengiriman->jenis = $request->jenis;
        $pengiriman->driver_id = $request->driver_id;
        $pengiriman->bon_sementara = $bs;
        $pengiriman->sewa = $sewa;

        $pengiriman->save();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function show($id)
    {
        $pengiriman = Pengiriman::find($id);

        return response()->json($pengiriman);
    }


    public function edit(Pengiriman $pengiriman)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $pengiriman = Pengiriman::find($id);
        $bs = preg_replace('/\D/', '', $request->bs);
        $sewa = preg_replace('/\D/', '', $request->sewa);

        $pengiriman->tanggal = $request->tanggal;
        $pengiriman->tipe_pengiriman = $request->tipe;
        $pengiriman->tujuan = $request->tujuan;
        $pengiriman->jenis = $request->jenis;
        $pengiriman->driver_id = $request->driver_id;
        $pengiriman->bon_sementara = $bs;
        $pengiriman->sewa = $sewa;
        $pengiriman->update();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function destroy($id)
    {
        $pengiriman = Pengiriman::find($id);
        $pengiriman->delete();

        return response(null, 204);
    }

    public function export($id)
    {
        $pengiriman = Pengiriman::with([
            'driver',
            'tipe',
            'tujuann',
            'pengeluaran.jenisPengeluaran'
        ])->findOrFail($id);

        $spreadsheet = IOFactory::load(storage_path('app/template/template.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $sheet->setCellValue('B13', ': Biaya Ekspedisi Pengiriman Produk ' . $pengiriman->jenis);
        $sheet->setCellValue('B15', ': ' . formatTanggalIndo($pengiriman->tanggal));

        $sheet->setCellValue('D11', ': ' . $pengiriman->tipe->nama . ' - ' . $pengiriman->tipe->plat);
        $sheet->setCellValue('D12', ': ' . $pengiriman->tujuann->nama);
        $sheet->setCellValue('D13', ': ' . $pengiriman->driver->nama);

        $sheet->setCellValue('D15', ': Rp. ' . number_format($pengiriman->bon_sementara));
        $sheet->setCellValue('F20', 'Rp. ' . number_format($pengiriman->bon_sementara));

        /* === Tulis Data Pengeluaran === */
        $row = 25;
        $no = 1;

        foreach ($pengiriman->pengeluaran as $pengeluaran) {
            if ($row > 46) break;

            $namaJenis = $pengeluaran->jenisPengeluaran->nama ?? '-';

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $namaJenis . ' ' . $pengeluaran->keterangan);
            $sheet->setCellValue("C{$row}", 'Rp. ' . number_format($pengeluaran->nominal));

            $row++;
        }

        $total = $pengiriman->pengeluaran->sum('nominal');
        $sheet->setCellValue('F49', 'Rp. ' . number_format($total));

        $selisih = $pengiriman->bon_sementara - $total;
        $sheet->setCellValue('F52', 'Rp. ' . number_format($selisih));

        // Warna merah jika minus
        $styleArray = [
            'font' => [
                'color' => ['rgb' => $selisih < 0 ? 'FF0000' : '000000'],
            ]
        ];
        $sheet->getStyle('F52')->applyFromArray($styleArray);

        $sheet->setCellValue('B66', 'Wonogiri, ' . formatTanggalIndo($pengiriman->tanggal));

        $filename = $pengiriman->tipe->nama . '-' . formatTanggalIndo($pengiriman->tanggal) . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
