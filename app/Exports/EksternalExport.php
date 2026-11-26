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

class EksternalExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithEvents
{
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $no = 1;

    public function __construct($tanggalAwal, $tanggalAkhir)
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function collection()
    {
        return Pengiriman::with([
            'tipe',
            'tujuann',
            'pengeluaran'
        ])
            ->whereBetween('tanggal', [
                $this->tanggalAwal,
                $this->tanggalAkhir
            ])
            ->whereHas('tipe', function ($q) {
                $q->where('nama', 'like', '%sewa eksternal%');
            })
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Tipe Pengiriman',
            'Tujuan',
            'Sewa',
        ];
    }


    public function map($row): array
    {
        return [
            $this->no++,
            formatTanggalIndo($row->tanggal),
            $row->tipe->nama ?? '-',
            $row->tujuann->nama ?? '-',
            $row->pengeluaran()->sum('nominal'),
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Geser header dan data 2 baris ke bawah
                $sheet->insertNewRowBefore(1, 2);

                // Judul
                $sheet->mergeCells('A1:E1');

                $sheet->setCellValue(
                    'A1',
                    'REKAP PENGIRIMAN EKSTERNAL PERIODE ' .
                        strtoupper(
                            \Carbon\Carbon::parse($this->tanggalAwal)
                                ->locale('id')
                                ->translatedFormat('F Y')
                        ) .
                        ' S/D ' .
                        strtoupper(
                            \Carbon\Carbon::parse($this->tanggalAkhir)
                                ->locale('id')
                                ->translatedFormat('F Y')
                        )
                );

                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(14);

                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(18);

                // Header sekarang di baris 3
                $sheet->getStyle('A3:E3')->getFont()->setBold(true);

                $sheet->getStyle('A:E')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A3:E3')->getFill()
                    ->setFillType(Fill::FILL_SOLID);

                $sheet->getStyle('A3:E3')->getFill()
                    ->getStartColor()
                    ->setARGB('D9E1F2');

                // Border seluruh tabel
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $sheet->getStyle("A3:{$highestCol}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Format angka kolom Pengeluaran
                $sheet->getStyle("E4:E{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            }
        ];
    }
}
