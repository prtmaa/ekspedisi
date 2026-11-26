    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="modal-form" style="overflow:hidden;" role="dialog"
        aria-labelledby="modal-form" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">

            <form action="" method="post" enctype="multipart/form-data" data-toggle="validator"
                class="form-horizontal">
                @csrf
                @method('post')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group row">
                            <label for="tanggal" class="col-md-4 col-md-offset-1 control-label">Tanggal</label>
                            <div class="col-md 6">
                                <input type="text" name="tanggal" id="tanggal" class="form-control tanggal"
                                    required autofocus oninvalid="this.setCustomValidity('Silahkan pilih tanggal')"
                                    oninput="this.setCustomValidity('')">
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="tipe" class="col-md-4 col-md-offset-1 control-label">Tipe
                                Pengiriman</label>
                            <div class="col-md 6">
                                <select name="tipe" id="tipe" class="form-control" required
                                    oninvalid="this.setCustomValidity('Silahkan pilih tipe pengiriman')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="" disabled selected>Pilih. . . </option>
                                    @foreach ($tipe as $key => $item)
                                        <option value="{{ $item->id }}">{{ $item->nama }} - {{ $item->plat }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="tujuan" class="col-md-4 col-md-offset-1 control-label">Tujuan</label>
                            <div class="col-md 6">
                                <select name="tujuan" id="tujuan" class="form-control" required>
                                    <option value="" disabled selected>Pilih. . . </option>
                                    @foreach ($tujuan as $item)
                                        <option value="{{ $item->id }}" data-sewa="{{ $item->sewa }}">
                                            {{ $item->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="jenis" class="col-md-4 col-md-offset-1 control-label">Jenis Muatan</label>
                            <div class="col-md 6">
                                <select name="jenis" id="jenis" class="form-control" required
                                    oninvalid="this.setCustomValidity('Silahkan pilih jenis muatan')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="" disabled selected>Pilih. . . </option>
                                    <option value="Fresh">Fresh</option>
                                    <option value="Frozen">Frozen</option>
                                </select>
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="driver_id" class="col-md-4 col-md-offset-1 control-label">Driver</label>
                            <div class="col-md 6">
                                <select name="driver_id" id="driver_id" class="form-control" required
                                    oninvalid="this.setCustomValidity('Silahkan pilih driver')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="" disabled selected>Pilih. . . </option>
                                    @foreach ($driver as $key => $item)
                                        <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                    @endforeach
                                </select>
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="sewa" class="col-md-4 col-md-offset-1 control-label">Sewa</label>
                            <div class="col-md 6">
                                <input type="text" name="sewa" id="sewa" class="form-control" required
                                    oninvalid="this.setCustomValidity('Sewa harus diisi')"
                                    oninput="this.setCustomValidity('')" autofocus>
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="bs" class="col-md-4 col-md-offset-1 control-label">Bon Sementara</label>
                            <div class="col-md 6">
                                <input type="text" name="bs" id="bs" class="form-control" required
                                    oninvalid="this.setCustomValidity('Bon sementara harus diisi')"
                                    oninput="this.setCustomValidity('')" autofocus>
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i
                                class="fa fa-xmark"></i> Batal</button>
                        <button class="btn btn-sm btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
