@extends('layouts.master')

@section('tittle')
    Data Pengeluaran (Bulanan)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"> <a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Finance</li>
    <li class="breadcrumb-item active">LR Ekspedisi</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <section class="col-lg-12 connectedSortable">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body table-responsive">

                                <div class="mb-3">
                                    <div class="row align-items-end">

                                        <!-- Pilih Bulan -->
                                        <div class="col-md-3">
                                            <label class="form-label mb-1">Bulan</label>
                                            <input type="month" id="bulan" class="form-control"
                                                value="{{ date('Y-m') }}">
                                        </div>

                                        <!-- Tujuan -->
                                        <div class="col-md-5">
                                            <label class="form-label mb-1">Tujuan</label>
                                            <select id="tujuan" name="tujuan[]" class="form-control" multiple>
                                                @foreach ($daftarTujuan as $t)
                                                    <option value="{{ $t->id }}"
                                                        {{ in_array($t->id, $defaultTujuan) ? 'selected' : '' }}>
                                                        {{ $t->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Tombol Export -->
                                        <div class="col-md-4 d-flex justify-content-end pe-2">
                                            <button id="btn-export" class="btn btn-success btn-sm">
                                                <i class="fas fa-file-excel"></i> Export Excel
                                            </button>
                                        </div>

                                    </div>
                                </div>


                                <table id="tableTujuan" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 20px;">No</th>
                                            <th>Tujuan</th>
                                            <th>Total Sewa</th>
                                            <th>Presentase</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-center">Total Pendapatan Sewa Ekspedisi</th>
                                            <th class="text-end"></th>
                                            <th class="text-end">100 %</th>
                                        </tr>
                                    </tfoot>

                                </table>

                            </div>

                        </div>

                        <div class="card">
                            <div class="card-body table-responsive">
                                <label>Cost Produksi</label>
                                <table id="tableJenis" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 20px;">No</th>
                                            <th>Jenis Pengeluaran</th>
                                            <th>Total</th>
                                            <th>Persen</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-center">Total Keseluruhan</th>
                                            <th></th>
                                            <th>100 %</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>


                        <div class="card">
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th style="width: 40%">Total Pendapatan</th>
                                            <td id="txtPendapatan" class="text-end">Rp 0</td>
                                        </tr>
                                        <tr>
                                            <th>Total Pengeluaran</th>
                                            <td id="txtPengeluaran" class="text-end">Rp 0</td>
                                        </tr>
                                        <tr class="table-primary fw-bold">
                                            <th>Laba Kotor</th>
                                            <td id="txtLaba" class="text-end">Rp 0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('rekap.form')
@endsection

@push('js')
    <script>
        $(function() {
            let table = $('#tableTujuan').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: false,
                info: false,
                ajax: {
                    url: '{{ route('rekap.tujuan.data') }}',
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                        d.tujuan = $('#tujuan').val(); // array
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'tujuan'
                    },
                    {
                        data: 'total_rupiah',
                        className: 'text-end'
                    },
                    {
                        data: 'persen_view',
                        className: 'text-end'
                    }
                ],

                footerCallback: function(row, data, start, end, display) {
                    let total = 0;

                    data.forEach(function(item) {
                        total += Number(item.total);
                    });

                    $('tfoot th:eq(1)').html(
                        'Rp ' + total.toLocaleString('id-ID')
                    );
                }

            });

            tableJenis = $('#tableJenis').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: false,
                info: false,
                ajax: {
                    url: '{{ route('rekap.dataJenis') }}',
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex'
                    },
                    {
                        data: 'jenis_pengeluaran'
                    },
                    {
                        data: 'total_rupiah',
                        className: 'text-end'
                    },
                    {
                        data: 'persen_view',
                        className: 'text-end'
                    }
                ],
                footerCallback: function(row, data) {
                    let total = 0;

                    data.forEach(function(item) {
                        // Ambil angka dari format Rupiah
                        let number = parseInt(String(item.total_rupiah).replace(/\D/g, ''));
                        if (!isNaN(number)) total += number;
                    });

                    // Update Total Pengeluaran (termasuk pajak)
                    $(this.api().column(2).footer())
                        .html('Rp ' + total.toLocaleString('id-ID'));

                    // Reload data laba karena total pengeluaran berubah
                    loadLaba();
                }
            });

            $('#bulan').on('change', function() {
                table.ajax.reload(); // table tujuan
                tableJenis.ajax.reload(); // table jenis + pajak
            });


            $('#tujuan').on('change', function() {
                table.ajax.reload();
            });

        });

        $('#tujuan').select2({
            placeholder: 'Pilih Tujuan',
            allowClear: true,
            closeOnSelect: false
        });

        function formatRupiah(angka) {
            return 'Rp ' + Number(angka).toLocaleString('id-ID');
        }


        function loadLaba() {
            $.get('{{ route('rekap.dataLaba') }}', {
                bulan: $('#bulan').val()
            }, function(res) {

                $('#txtPendapatan').html(formatRupiah(res.total_tujuan));
                $('#txtPengeluaran').html(formatRupiah(res.total_pengeluaran));
                $('#txtLaba').html(formatRupiah(res.total_laba));
            });
        }

        // load awal
        loadLaba();

        // reload saat bulan berubah
        $('#bulan').on('change', function() {
            loadLaba();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
