<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{

    public function index()
    {
        return view('driver.index');
    }

    public function data()
    {
        $driver = Driver::get();

        return datatables()
            ->of($driver)
            ->addIndexColumn()
            ->addColumn('aksi', function ($driver) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`' . route('driver.update', $driver->id) . '`)" class="btn btn-sm btn-info btn-flat"><i class="fa fa-pen"></i></button>
                     <button type="button" onclick="deleteData(`' . route('driver.destroy', $driver->id) . '`)" class="btn btn-sm btn-danger btn-flat"><i class="fa fa-trash"></i></button>
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

        $driver = new Driver();

        $driver->nama = $request->nama;

        $driver->save();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function show($id)
    {
        $driver = Driver::find($id);

        return response()->json($driver);
    }


    public function edit(Driver $driver)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $driver = Driver::find($id);
        $driver->nama = $request->nama;
        $driver->update();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function destroy($id)
    {
        $driver = Driver::find($id);
        $driver->delete();

        return response(null, 204);
    }
}
