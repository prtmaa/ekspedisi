    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="modal-form" role="dialog" aria-labelledby="modal-form"
        aria-hidden="true">
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
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">


                        <div class="row">

                            <form action="" method="POST">
                                @csrf
                                <table class="table table-bordered" id="pengeluaranTable">
                                    <thead>
                                        <tr>
                                            <th>Jenis Pengeluaran</th>
                                            <th>Nominal</th>
                                            <th>Keterangan</th>
                                            <th><button type="button" class="btn btn-success btn-sm" id="addRow"><i
                                                        class="fas fa-plus"></i></button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="jenis_pengeluaran[]" class="form-control" required>
                                                    <option value="" disabled selected>
                                                        Pilih Jenis Pengeluaran
                                                    </option>
                                                    @foreach ($jenis as $key => $item)
                                                        <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="nominal[]" class="form-control rupiah"
                                                    required autocomplete="off">
                                            </td>
                                            <td><input type="text" name="keterangan[]" class="form-control"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm removeRow"><i
                                                        class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                        </div>
            </form>
        </div>

        <script>
            // Fungsi format angka ke format Rupiah (ribuan pake titik)
            function formatRupiah(angka) {
                let numberString = angka.replace(/[^,\d]/g, '').toString();
                let split = numberString.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah;
            }

            // Pasang event listener ke semua input dengan class 'rupiah'
            function setupRupiahInputs() {
                const inputs = document.querySelectorAll('input.rupiah');
                inputs.forEach(input => {
                    input.addEventListener('input', function(e) {
                        this.value = formatRupiah(this.value);
                    });
                });
            }

            // Jalankan saat halaman siap
            setupRupiahInputs();

            // Event untuk tambah baris baru
            document.getElementById('addRow').addEventListener('click', function() {
                const table = document.querySelector('#pengeluaranTable tbody');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
        <td>
            <select name="jenis_pengeluaran[]" class="form-control" required>
                <option value="" disabled selected>Pilih Jenis Pengeluaran</option>
                   @foreach ($jenis as $key => $item)
                                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                                @endforeach
            </select>
        </td>
        <td><input type="text" name="nominal[]" class="form-control rupiah" required autocomplete="off"></td>
        <td><input type="text" name="keterangan[]" class="form-control"></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow"><i
                                                        class="fas fa-trash"></i></button></td>
    `;
                table.appendChild(newRow);
                setupRupiahInputs();
            });


            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('removeRow')) {
                    e.target.closest('tr').remove();
                }
            });

            document.querySelector('form.form-horizontal').addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input.rupiah');
                inputs.forEach(input => {
                    input.value = input.value.replace(/\./g, '').replace(/,/g, '.');
                });
            });
        </script>

        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-xmark"></i>
                Batal</button>
            <button class="btn btn-sm btn-primary"><i class="fa fa-save"></i> Simpan</button>
        </div>
    </div>
    </form>

    </div>
    </div>
