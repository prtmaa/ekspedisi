<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Pengiriman;
use App\Models\Tipe;
use Illuminate\Http\Request;
use App\Models\Tujuan;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\PengirimanBulananExport;
use Carbon\Carbon;

class PengirimanController extends Controller
{
    public function index()
    {
        $driver = Driver::get();
        $tipe = Tipe::get();
        $tujuan = Tujuan::get();

        return view('pengiriman.index', compact('driver', 'tipe', 'tujuan'));
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
                return '<a href="' . route('pengeluaran.index', $pengiriman->id) . '">' . formatTanggalIndo($pengiriman->tanggal) . '</a>';
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
            ->addColumn('titik', function ($row) {
                return formatRupiah($row->titik);
            })
            ->addColumn('bon_sementara', function ($row) {
                return formatRupiah($row->bon_sementara);
            })
            ->addColumn('pengeluaran', function ($row) {
                $totalPengeluaran = $row->pengeluaran()->sum('nominal');
                return formatRupiah($totalPengeluaran);
            })
            ->addColumn('sisa', function ($row) {

                $totalPengeluaran = $row->pengeluaran()
                    ->whereHas('jenisPengeluaran', function ($q) {
                        $q->whereIn('role', ['warehouse']);
                    })
                    ->sum('nominal');

                $sisa = $row->bon_sementara - $totalPengeluaran;

                return formatRupiah($sisa);
            })

            ->addColumn('aksi', function ($pengiriman) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`' . route('pengiriman.update', $pengiriman->id) . '`)" class="btn btn-sm btn-info btn-flat"><i class="fa fa-pen"></i></button>
                    <button type="button" onclick="deleteData(`' . route('pengiriman.destroy', $pengiriman->id) . '`)" class="btn btn-sm btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                    <a href="' . route('pengiriman.export', $pengiriman->id) . '" class="btn btn-sm btn-success btn-flat"><i class="fa fa-file-excel"></i></a>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'tanggal'])
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
        $titik = preg_replace('/\D/', '', $request->titik);


        $pengiriman->tanggal = $request->tanggal;
        $pengiriman->tipe_pengiriman = $request->tipe;
        $pengiriman->tujuan = $request->tujuan;
        $pengiriman->jenis = $request->jenis;
        $pengiriman->driver_id = $request->driver_id;
        $pengiriman->bon_sementara = $bs;
        $pengiriman->sewa = $sewa;
        $pengiriman->titik = $titik;

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
        $titik = preg_replace('/\D/', '', $request->titik);

        $pengiriman->tanggal = $request->tanggal;
        $pengiriman->tipe_pengiriman = $request->tipe;
        $pengiriman->tujuan = $request->tujuan;
        $pengiriman->jenis = $request->jenis;
        $pengiriman->driver_id = $request->driver_id;
        $pengiriman->bon_sementara = $bs;
        $pengiriman->sewa = $sewa;
        $pengiriman->titik = $titik;
        $pengiriman->update();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function destroy($id)
    {
        $pengiriman = Pengiriman::find($id);
        $pengiriman->delete();

        return response(null, 204);
    }

    public function exportBulanan(Request $request)
    {
        $bulan = Carbon::createFromFormat('Y-m', $request->bulan);

        return Excel::download(
            new PengirimanBulananExport(
                $bulan->month,
                $bulan->year
            ),
            'pengiriman_' . $bulan->format('Y_m') . '.xlsx'
        );
    }

    public function export($id)
    {
        $pengiriman = Pengiriman::with([
            'driver',
            'tipe',
            'tujuann',
            'pengeluaran' => function ($q) {
                $q->whereHas('jenisPengeluaran', function ($j) {
                    $j->whereRaw('LOWER(role) = ?', ['warehouse']);
                });
            },
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
        $sheet->setCellValue('F21', 'Rp. ' . number_format($pengiriman->bon_sementara));

        /* === Sewa, Tambah Titik, Total (baris 17-19, sesuai label template) === */
        $sewa  = (float) $pengiriman->sewa;
        $titik = (float) $pengiriman->titik;

        $sheet->setCellValue('D17', ': Rp. ' . number_format($sewa));
        $sheet->setCellValue('D18', $titik > 0 ? ': Rp. ' . number_format($titik) : '');
        $sheet->setCellValue('D19', ': Rp. ' . number_format($sewa + $titik));

        // Label C18 dikosongkan kalau tidak ada tambah titik
        if ($titik <= 0) {
            $sheet->setCellValue('C18', '');
        }

        // Bold, sama seperti D11-D15
        $sheet->getStyle('D17:D19')->getFont()->setBold(true);

        // Label di C17:C19 terpotong karena kolom C sempit, jadi dilebarkan
        // (B dikurangi sebanyak itu supaya lebar halaman cetak tetap sama)
        $sheet->getColumnDimension('B')->setWidth(73);
        $sheet->getColumnDimension('C')->setWidth(21.6);

        /* === Tulis Data Pengeluaran === */
        $row = 25;
        $no = 1;

        foreach ($pengiriman->pengeluaran as $pengeluaran) {
            if ($row > 49) break;

            $namaJenis = $pengeluaran->jenisPengeluaran->nama ?? '-';

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $namaJenis . ' ' . $pengeluaran->keterangan);
            $sheet->setCellValue("C{$row}", 'Rp. ' . number_format($pengeluaran->nominal));

            $row++;
        }

        $total = $pengiriman->pengeluaran->sum('nominal');
        $sheet->setCellValue('F50', 'Rp. ' . number_format($total));

        $selisih = $pengiriman->bon_sementara - $total;
        $sheet->setCellValue('F53', 'Rp. ' . number_format($selisih));

        // Warna merah jika minus
        $sheet->getStyle('F53')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => $selisih < 0 ? 'FF0000' : '000000'],
            ],
        ]);

        $sheet->setCellValue('B66', 'Wonogiri, ' . formatTanggalIndo($pengiriman->tanggal));
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
