@extends('layouts.master')

@section('tittle')
    Data Pengiriman Eksternal
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"> <a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rekap</li>
    <li class="breadcrumb-item active">Eksternal</li>
@endsection

@section('content')
    <div class="container-fluid">

        <div class="row">

            <section class="col-lg-12 connectedSortable">

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">

                                
                                <div class="d-flex flex-wrap align-items-center">

                                    <div class="d-inline-flex gap-2">
                                        <input type="text" id="bulan_awal" class="form-control" placeholder="Bulan Awal"
                                            style="width: 150px">

                                        <input type="text" id="bulan_akhir" class="form-control"
                                            placeholder="Bulan Akhir" style="width: 150px">
                                    </div>

                                    <button id="btn-export" class="btn btn-success btn-sm">
                                        <i class="fa fa-file-excel"></i> Export
                                    </button>

                                </div>


                            </div>

                            <div class="card-body table-responsive">
                                <form action="" class="form-produk" method="post">
                                    @csrf
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <th style="width: 20px;">No</th>
                                            <th>Tanggal</th>
                                            <th>Tipe</th>
                                            <th>Tujuan</th>
                                            <th>Sewa</th>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </form>
                            </div>

                        </div>

                    </div>

                </div>

            </section>
        </div>
    @endsection

    @push('js')
        <script>
            let table;
            $(function() {
                table = $('.table').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    autoWidth: false,
                    responsive: true,
                    "language": {
                        "sProcessing": "Sedang memproses...",
                        "sLengthMenu": "Tampilkan _MENU_ entri",
                        "sZeroRecords": "Tidak ditemukan data yang sesuai",
                        "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                        "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                        "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                        "sSearch": "Pencarian:",
                        "oPaginate": {
                            "sFirst": "Pertama",
                            "sPrevious": "Sebelumnya",
                            "sNext": "Selanjutnya",
                            "sLast": "Terakhir"
                        },
                    },
                    ajax: {
                        url: '{{ route('eksternal.data') }}',
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            searchable: false
                        },
                        {
                            data: 'tanggal'
                        },
                        {
                            data: 'tipe_pengiriman'
                        },
                        {
                            data: 'tujuan'
                        },
                        {
                            data: 'pengeluaran',
                        },
                    ]
                });

            });
        </script>
        <script>
             flatpickr("#bulan_awal", {
                dateFormat: "Y-m",
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y-m",
                        altFormat: "F Y"
                    })
                ]
            });

            flatpickr("#bulan_akhir", {
                dateFormat: "Y-m",
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y-m",
                        altFormat: "F Y"
                    })
                ]
            });

            $('#btn-export').on('click', function() {

                let bulan_awal = $('#bulan_awal').val();
                let bulan_akhir = $('#bulan_akhir').val();

                if (!bulan_awal || !bulan_akhir) {
                    alert('Pilih rentang bulan terlebih dahulu');
                    return;
                }

                window.location.href =
                    "{{ route('eksternal.export.bulanan') }}" +
                    "?bulan_awal=" + bulan_awal +
                    "&bulan_akhir=" + bulan_akhir;
            });
        </script>
    @endpush
