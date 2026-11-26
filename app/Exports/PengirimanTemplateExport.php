<?php

namespace App\Exports;

use App\Models\Pengiriman;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTempFile;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Contracts\View\View;

class PengirimanTemplateExport implements WithEvents, WithTitle
{
    protected $pengiriman;

    public function __construct($pengiriman)
    {
        $this->pengiriman = $pengiriman;
    }

    public function title(): string
    {
        return 'Laporan Pengeluaran';
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $pengiriman = $this->pengiriman;
                $spreadsheet = IOFactory::load(storage_path('app/template/template.xlsx'));
                $sheet = $spreadsheet->getActiveSheet();

                // Nama perusahaan dan alamat
                $sheet->setCellValue('A2', 'PT. Contoh Perusahaan');
                $sheet->setCellValue('A3', 'Jl. Contoh Alamat');

                // Nama surat
                $sheet->setCellValue('A8', 'Laporan Pengeluaran');

                // Nama dan divisi
                $sheet->setCellValue('A10', 'Nama: ' . $pengiriman->driver->nama);
                $sheet->setCellValue('A11', 'Divisi: Operasional');

                // Tipe pengiriman
                $sheet->setCellValue('A13', 'Tipe: ' . $pengiriman->tipe->nama);

                // Bon sementara
                $sheet->setCellValue('A14', 'Bon: ' . number_format($pengiriman->bon_sementara));
                $sheet->setCellValue('A20', 'Bon Asli: ' . number_format($pengiriman->bon_sementara));

                // Judul Realisasi
                $sheet->setCellValue('A23', 'Realisasi Pembelian');
                $sheet->setCellValue('A24', 'Pengeluaran');
                $sheet->setCellValue('B24', 'Nominal');

                // List pengeluaran
                $row = 25;
                foreach ($pengiriman->pengeluaran as $pengeluaran) {
                    if ($row > 46) break; // Batas maksimal
                    $sheet->setCellValue("A{$row}", $pengeluaran->jenis_pengeluaran);
                    $sheet->setCellValue("B{$row}", $pengeluaran->nominal);
                    $row++;
                }

                // Total
                $total = $pengiriman->pengeluaran->sum('nominal');
                $sheet->setCellValue('A49', 'Total Pengeluaran:');
                $sheet->setCellValue('B49', $total);

                // Sisa uang
                $sheet->setCellValue('A52', 'Sisa Uang:');
                $sheet->setCellValue('B52', $pengiriman->bon_sementara - $total);

                // Tanggal
                $sheet->setCellValue('B66', now()->format('d-m-Y'));

                // Inject sheet ke writer
                $event->writer->setSpreadsheet($spreadsheet);
            }
        ];
    }

    // public function drawings()
    // {
    //     $drawing = new Drawing();
    //     $drawing->setName('Logo');
    //     $drawing->setPath(public_path('images/logo.png')); // ganti path sesuai logo kamu
    //     $drawing->setHeight(80);
    //     $drawing->setCoordinates('B2');
    //     return [$drawing];
    // }
}
