<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="modal-form-edit" style="overflow:hidden;" role="dialog"
    aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">

        <form action="" method="post" enctype="multipart/form-data" data-toggle="validator" class="form-horizontal">
            @csrf
            @method('post')

            <input type="hidden" name="_method" value="put">


            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <form action="" method="POST">
                            @csrf
                            <table class="table table-bordered" id="pengeluaranTable">
                                <thead>
                                    <tr>
                                        <th>Jenis Pengeluaran</th>
                                        <th>Nominal</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="jenis_pengeluaran" class="form-control" required>
                                                <option value="" disabled selected>
                                                    Pilih Jenis Pengeluaran
                                                </option>

                                                @foreach ($jenis as $key => $item)
                                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" name="nominal" class="form-control"
                                                required></td>
                                        <td><input type="text" name="keterangan" class="form-control"></td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-xmark"></i>
            Batal</button>
        <button class="btn btn-sm btn-primary"><i class="fa fa-save"></i> Simpan</button>
    </div>
</div>
</form>

</div>
</div>
