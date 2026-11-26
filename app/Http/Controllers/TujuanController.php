<?php

namespace App\Http\Controllers;

use App\Models\Tujuan;
use Illuminate\Http\Request;

class TujuanController extends Controller
{
    public function index()
    {
        return view('tujuan.index');
    }

    public function data()
    {
        $tujuan = Tujuan::get();

        return datatables()
            ->of($tujuan)
            ->addIndexColumn()
            ->addColumn('sewa', function ($row) {
                return formatRupiah($row->sewa);
            })
            ->addColumn('aksi', function ($tujuan) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`' . route('tujuan.update', $tujuan->id) . '`)" class="btn btn-sm btn-info btn-flat"><i class="fa fa-pen"></i></button>
                     <button type="button" onclick="deleteData(`' . route('tujuan.destroy', $tujuan->id) . '`)" class="btn btn-sm btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $tujuan = new tujuan();

        $sewa = preg_replace('/\D/', '', $request->sewa);

        $tujuan->nama = $request->nama;
        $tujuan->sewa = $sewa;

        $tujuan->save();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function show($id)
    {
        $tujuan = Tujuan::find($id);

        return response()->json($tujuan);
    }


    public function edit(tujuan $tujuan)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $tujuan = Tujuan::find($id);

        $sewa = preg_replace('/\D/', '', $request->sewa);

        $tujuan->nama = $request->nama;
        $tujuan->sewa = $sewa;
        $tujuan->update();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function destroy($id)
    {
        $tujuan = Tujuan::find($id);
        $tujuan->delete();

        return response(null, 204);
    }
}
