<?php

namespace App\Http\Controllers;

use App\Models\JenisPenegeluaran;
use Illuminate\Http\Request;

class JenisPengeluaranController extends Controller
{
    public function index()
    {
        return view('jenispengeluaran.index');
    }

    public function data()
    {
        $jenis = JenisPenegeluaran::get();

        return datatables()
            ->of($jenis)
            ->addIndexColumn()
            ->addColumn('aksi', function ($jenis) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`' . route('jenis.update', $jenis->id) . '`)" class="btn btn-sm btn-info btn-flat"><i class="fa fa-pen"></i></button>
                     <button type="button" onclick="deleteData(`' . route('jenis.destroy', $jenis->id) . '`)" class="btn btn-sm btn-danger btn-flat"><i class="fa fa-trash"></i></button>
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

        $jenis = new JenisPenegeluaran();

        $jenis->nama = $request->nama;
        $jenis->role = $request->role;


        $jenis->save();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function show($id)
    {
        $jenis = JenisPenegeluaran::find($id);

        return response()->json($jenis);
    }


    public function update(Request $request, $id)
    {
        $jenis = JenisPenegeluaran::find($id);
        $jenis->nama = $request->nama;
        $jenis->role = $request->role;

        $jenis->update();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function destroy($id)
    {
        $jenis = JenisPenegeluaran::find($id);
        $jenis->delete();

        return response(null, 204);
    }
}
