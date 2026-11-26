<?php

namespace App\Http\Controllers;

use App\Models\Pajak;
use Illuminate\Http\Request;

class CostController extends Controller
{
    public function index()
    {
        return view('finance.cost.index');
    }

    public function data()
    {
        $cost = Pajak::get();

        return datatables()
            ->of($cost)
            ->addIndexColumn()

            // 🔹 Format kolom nilai pajak
            ->addColumn('nilai_view', function ($row) {

                if (str_contains(strtolower($row->nama), 'ppn')) {
                    // Format dalam persen
                    return $row->persen . ' %';
                }

                if (
                    str_contains(strtolower($row->nama), 'susut') ||
                    str_contains(strtolower($row->nama), 'depresiasi')
                ) {
                    // Format dalam rupiah
                    return 'Rp ' . number_format($row->persen, 0, ',', '.');
                }

                // Default (misal pajak lain yg tetap pakai persen)
                return $row->persen . ' %';
            })

            ->addColumn('aksi', function ($cost) {
                return '
            <div class="btn-group">
                <button type="button"
                    onclick="editForm(`' . route('cost.update', $cost->id) . '`)"
                    class="btn btn-sm btn-info btn-flat">
                    <i class="fa fa-pen"></i>
                </button>
            </div>';
            })

            ->rawColumns(['aksi'])
            ->make(true);
    }


    public function show($id)
    {
        $cost = Pajak::find($id);

        return response()->json($cost);
    }


    public function update(Request $request, $id)
    {
        $cost = Pajak::find($id);
        $cost->nama = $request->nama;
        $cost->persen = $request->persen;

        $cost->update();

        return response()->json('Data berhasil disimpan', 200);
    }
}
