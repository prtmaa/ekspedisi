@extends('layouts.master')

@section('tittle')
    Data Pengeluaran (Per Tipe)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"> <a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rekap</li>
    <li class="breadcrumb-item active">Per Tipe</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <section class="col-lg-12 connectedSortable">
                <div class="row">
                    <div class="col-md-12">

                        <div class="card">

                            <div class="card-body table-responsive">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="row align-items-center w-100">
                                        <div class="col-md-5">
                                            <div class="form-group mb-0 d-flex align-items-center">
                                                <label class="col-md-2 mb-0">Periode:</label>
                                                <input type="date" id="start_date" name="start_date"
                                                    class="form-control me-2" value="{{ date('Y-m-01') }}">
                                                <label for="" class="mr-2 ml-2">s/d</label>
                                                <input type="date" id="end_date" name="end_date" class="form-control"
                                                    value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>


                                        <div class="col-md-auto">
                                            <button id="btn-export" class="btn btn-success btn-sm">
                                                <i class="fas fa-file-excel"></i> Export Excel
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-produk">
                                    <table class="tablebulan table-striped table-bordered">
                                        <thead>
                                            <th style="width: 20px;">No</th>
                                            <th>Tipe</th>
                                            <th>Sewa</th>
                                            <th>Pengeluaran</th>
                                            <th>Pendapatan</th>

                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" class="text-end">Total Keseluruhan:</th>
                                                <th id="total-sewa"></th>
                                                <th id="total-pengeluaran"></th>
                                                <th id="total-pendapatan"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Grafik Pendapatan per Tipe</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="chartPendapatan" height="100"></canvas>
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
        let tablebulan;

        function formatRupiah(angka) {
            angka = Number(angka) || 0;
            return 'Rp ' + angka.toLocaleString('id-ID');
        }

        $(function() {
            tablebulan = $('.tablebulan').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                autoWidth: false,
                responsive: true,
                searching: false,
                paging: false,
                language: {
                    sProcessing: "Sedang memproses...",
                    sLengthMenu: "Tampilkan _MENU_ entri",
                    sZeroRecords: "Tidak ditemukan data",
                    sInfo: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    sInfoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    sSearch: "Pencarian:",
                    oPaginate: {
                        sFirst: "Pertama",
                        sPrevious: "Sebelumnya",
                        sNext: "Selanjutnya",
                        sLast: "Terakhir"
                    },
                },
                order: [],
                ajax: {
                    url: '{{ route('rekap.tipebulan') }}',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    },
                    dataSrc: function(json) {
                        renderChart(json.data); // update chart setelah data diterima
                        return json.data;
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'tipe',
                        orderable: false
                    },
                    {
                        data: 'sewa_rupiah',
                        orderable: false
                    },
                    {
                        data: 'pengeluaran_rupiah',
                        orderable: false
                    },
                    {
                        data: 'pendapatan_rupiah',
                        orderable: false
                    }
                ],

                footerCallback: function(row, data) {
                    let totalSewa = 0,
                        totalPengeluaran = 0,
                        totalPendapatan = 0;
                    data.forEach(function(item) {
                        totalSewa += parseInt(item.sewa || 0);
                        totalPengeluaran += parseInt(item.pengeluaran || 0);
                        totalPendapatan += parseInt(item.pendapatan || 0);
                    });

                    $('#total-sewa').html(formatRupiah(totalSewa));
                    $('#total-pengeluaran').html(formatRupiah(totalPengeluaran));
                    $('#total-pendapatan').html(formatRupiah(totalPendapatan));
                }

            });


            $('#start_date, #end_date').on('change', function() {
                tablebulan.ajax.reload();
            });

        });

        let chartPendapatan;

        function renderChart(data) {
            let labels = data.map(item => item.tipe);
            let sewa = data.map(item => item.sewa);
            let pengeluaran = data.map(item => item.pengeluaran);
            let pendapatan = data.map(item => item.pendapatan);

            let ctx = document.getElementById('chartPendapatan').getContext('2d');

            if (chartPendapatan) {
                chartPendapatan.destroy();
            }

            chartPendapatan = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Sewa',
                            data: sewa,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)', // biru
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Pengeluaran',
                            data: pengeluaran,
                            backgroundColor: 'rgba(255, 99, 132, 0.6)', // merah
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Pendapatan',
                            data: pendapatan,
                            backgroundColor: 'rgba(40, 167, 69, 0.6)', // hijau
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw || 0;
                                    return context.dataset.label + ': Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    let val = Number(value) || 0;
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                autoSkip: false
                            }
                        }]
                    }


                }
            });
        }

        $('#btn-export').on('click', function() {
            let start = $('#start_date').val();
            let end = $('#end_date').val();

            let url = "{{ route('rekap.tipe.export') }}?start_date=" + start + "&end_date=" + end;
            window.location.href = url;
        });
    </script>
@endpush
