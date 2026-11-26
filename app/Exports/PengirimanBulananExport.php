<?php

namespace App\Exports;

use App\Models\JenisPenegeluaran;
use App\Models\Pengiriman;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PengirimanBulananExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $jenisPengeluaran;
    protected $no = 1;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->jenisPengeluaran = JenisPenegeluaran::orderBy('nama')->get();
    }

    public function collection()
    {
        return Pengiriman::with([
            'tujuann',
            'tipe',
            'pengeluaran'
        ])
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal')
            ->get();
    }

    public function headings(): array
    {
        $head = [
            'No',
            'Tanggal',
            'Tujuan',
            'Sewa',
            'Bon Sementara',
        ];

        foreach ($this->jenisPengeluaran as $jenis) {
            $head[] = $jenis->nama;
        }

        $head[] = 'Total Pengeluaran';
        $head[] = 'Sisa';
        $head[] = 'Mobil';

        return $head;
    }


    public function map($row): array
    {
        $data = [
            $this->no++,
            formatTanggalIndo($row->tanggal),
            $row->tujuann->nama ?? '-',
            $row->sewa,
            $row->bon_sementara,
        ];

        $totalPengeluaran = 0;

        foreach ($this->jenisPengeluaran as $jenis) {
            $nominal = $row->pengeluaran
                ->where('jenis_pengeluaran_id', $jenis->id)
                ->sum('nominal');

            $data[] = $nominal;
            $totalPengeluaran += $nominal;
        }

        $sisa = $row->bon_sementara - $totalPengeluaran;

        $data[] = $totalPengeluaran;
        $data[] = $sisa;
        $data[] = $row->tipe->plat ?? '-';

        return $data;
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(18);  // Tanggal
                $sheet->getColumnDimension('C')->setWidth(23);  // Tujuan
                $sheet->getColumnDimension('D')->setWidth(13);  // Sewa
                $sheet->getColumnDimension('E')->setWidth(13);  // Bon Sementara

                $startJenis = 6; // kolom F
                foreach ($this->jenisPengeluaran as $i => $jenis) {
                    $col = Coordinate::stringFromColumnIndex($startJenis + $i);
                    $sheet->getColumnDimension($col)->setWidth(13);
                }

                // jumlah kolom
                $totalKolom = 5 + $this->jenisPengeluaran->count() + 2;

                // index kolom awal & akhir jenis pengeluaran
                $startJenis = 6; // kolom F
                $endJenis   = 5 + $this->jenisPengeluaran->count();

                $startCol = Coordinate::stringFromColumnIndex($startJenis);
                $endCol   = Coordinate::stringFromColumnIndex($endJenis);

                /**
                 * HEADER BIAYA OPERASIONAL
                 */
                $sheet->insertNewRowBefore(1, 1);
                $sheet->mergeCells("{$startCol}1:{$endCol}1");
                $sheet->setCellValue("{$startCol}1", 'Biaya Oprasional');
                $sheet->getStyle("{$startCol}1")->getFont()->setBold(true);
                $sheet->getStyle("{$startCol}1")->getAlignment()->setHorizontal('center');

                /**
                 * FOOTER TOTAL
                 */
                $lastDataRow = $sheet->getHighestRow() + 1;

                $sheet->setCellValue("A{$lastDataRow}", 'Total');
                $sheet->mergeCells("A{$lastDataRow}:C{$lastDataRow}");
                $sheet->getStyle("A{$lastDataRow}")->getFont()->setBold(true);

                for ($col = 4; $col <= $totalKolom; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->setCellValue(
                        "{$colLetter}{$lastDataRow}",
                        "=SUM({$colLetter}3:{$colLetter}" . ($lastDataRow - 1) . ")"
                    );
                    $sheet->getStyle("{$colLetter}{$lastDataRow}")->getFont()->setBold(true);
                }

                $colTotal = 6 + $this->jenisPengeluaran->count();
                $colSisa  = $colTotal + 1;
                $colMobil = $colSisa + 1;

                $colTotalL = Coordinate::stringFromColumnIndex($colTotal);
                $colSisaL  = Coordinate::stringFromColumnIndex($colSisa);
                $colMobilL = Coordinate::stringFromColumnIndex($colMobil);

                $sheet->getColumnDimension($colTotalL)->setWidth(13);
                $sheet->getColumnDimension($colSisaL)->setWidth(13);
                $sheet->getColumnDimension($colMobilL)->setWidth(11);

                foreach (['A', 'B', 'C', 'D', 'E', $colTotalL, $colSisaL, $colMobilL] as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                    $sheet->setCellValue("{$col}1", $sheet->getCell("{$col}2")->getValue());
                }

                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Tanggal');
                $sheet->setCellValue('C1', 'Tujuan');
                $sheet->setCellValue('D1', 'Sewa');
                $sheet->setCellValue('E1', 'BS');
                $sheet->setCellValue("{$colTotalL}1", 'Total');
                $sheet->setCellValue("{$colSisaL}1", 'Sisa');
                $sheet->setCellValue("{$colMobilL}1", 'Mobil');

                foreach (['A1', 'B1', 'C1', 'D1', 'E1', "{$colTotalL}1", "{$colSisaL}1", "{$colMobilL}1"] as $cell) {
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }

                foreach (['F2', 'G2', 'H2', 'I2', 'J2', 'K2', 'L2'] as $cell) {
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $headerRange = "A1:{$sheet->getHighestColumn()}2";

                // Background
                $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle($headerRange)->getFill()->getStartColor()->setARGB('D9E1F2');

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("D3:{$highestCol}{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            }
        ];
    }
}
