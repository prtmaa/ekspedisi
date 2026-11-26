<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $pengiriman = DB::table('pengiriman')->get();

        foreach ($pengiriman as $p) {
            // ambil data sewa dari tabel tujuan
            $sewa = DB::table('tujuan')
                ->where('id', $p->tujuan)
                ->value('sewa');

            // update tabel pengiriman
            DB::table('pengiriman')
                ->where('id', $p->id)
                ->update(['sewa' => $sewa ?? 0]);
        }
    }

    public function down()
    {
        // Tidak perlu isi rollback untuk data
    }
};
