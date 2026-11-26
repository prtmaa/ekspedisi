<?php

namespace App\Http\Controllers;

use App\Models\JenisPenegeluaran;
use App\Models\Pengeluaran;
use App\Models\Pengiriman;
use Illuminate\Http\Request;

class PengeluaranfinController extends Controller
{
    public function index($pengiriman_id)
    {
        $jenis = JenisPenegeluaran::whereIn('role', ['finance'])
            ->orderBy('nama')
            ->get();

        $pengiriman = Pengiriman::with('driver')->findOrFail($pengiriman_id);

        $sisaData = $this->hitungSisa($pengiriman_id);

        return view('finance.pengeluaran.index', [
            'pengiriman' => $pengiriman,
            'sisa' => $sisaData['sisa'],
            'jenis' => $jenis
        ]);
    }


    private function hitungSisa($pengiriman_id)
    {
        $totalPengeluaran = Pengeluaran::join(
            'jenis_pengeluarans',
            'pengeluaran.jenis_pengeluaran_id',
            '=',
            'jenis_pengeluarans.id'
        )
            ->where('pengeluaran.pengiriman_id', $pengiriman_id)
            ->sum('pengeluaran.nominal');

        $bonSementara = Pengiriman::findOrFail($pengiriman_id)->bon_sementara;

        $sisa = $bonSementara - $totalPengeluaran;

        return [
            'sisa' => $sisa,
            'sisa_formatted' => formatRupiah($sisa),
        ];
    }

    public function data($pengiriman_id)
    {
        $pengeluaran = Pengeluaran::with('jenisPengeluaran')
            ->where('pengiriman_id', $pengiriman_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return datatables()
            ->of($pengeluaran)
            ->addIndexColumn()
            ->addColumn('jenis', function ($row) {
                return $row->jenisPengeluaran->nama ?? '-';
            })
            ->addColumn('nominal', function ($row) {
                return formatRupiah($row->nominal);
            })
            ->addColumn('aksi', function ($row) {

                if (
                    $row->jenisPengeluaran &&
                    strtolower($row->jenisPengeluaran->role ?? '') === 'warehouse'
                ) {
                    return '
                    <div class="btn-group">
                        <button type="button"
                            class="btn btn-sm btn-secondary btn-flat">
                            <i class="fa fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-secondary btn-flat">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                ';
                }

                return '
                    <div class="btn-group">
                        <button type="button"
                            onclick="editForm(`' . route('pengeluaranfin.show', $row->id) . '`,
                                            `' . route('pengeluaranfin.update', $row->id) . '`)"
                            class="btn btn-sm btn-info btn-flat">
                            <i class="fa fa-pen"></i>
                        </button>

                        <button type="button"
                            onclick="deleteData(`' . route('pengeluaranfin.destroy', $row->id) . '`)"
                            class="btn btn-sm btn-danger btn-flat">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }


    public function create($pengiriman_id)
    {
        $pengiriman = Pengiriman::with('driver')->findOrFail($pengiriman_id);
        return view('finance.pengeluaran.form', compact('pengiriman'));
    }

    public function store(Request $request, $pengiriman_id)
    {
        $request->validate([
            'nominal.*' => 'required|numeric|min:0',
            'keterangan.*' => 'nullable|string',
        ]);

        $data = [];
        foreach ($request->jenis_pengeluaran as $index => $jenis) {
            $data[] = [
                'pengiriman_id' => $pengiriman_id,
                'jenis_pengeluaran_id' => $jenis,
                'nominal' => $request->nominal[$index],
                'keterangan' => $request->keterangan[$index],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Pengeluaran::insert($data);

        return response()->json(
            $this->hitungSisa($pengiriman_id)
        );
    }


    public function show($id)
    {
        $pengeluaran = Pengeluaran::find($id);

        return response()->json($pengeluaran);
    }

    public function update(Request $request, $id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);

        $pengeluaran->update([
            'jenis_pengeluaran_id' => $request->jenis_pengeluaran,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(
            $this->hitungSisa($pengeluaran->pengiriman_id)
        );
    }


    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        $pengiriman_id = $pengeluaran->pengiriman_id;

        $pengeluaran->delete();

        return response()->json(
            $this->hitungSisa($pengiriman_id)
        );
    }
}
