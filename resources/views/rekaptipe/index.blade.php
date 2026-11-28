@extends('layouts.master')

@section('tittle')
    Rekap Per Ekspedisi
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
                                                <th colspan="2" class="text-end">Total Keseluruhan Sebelum Pajak:</th>
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
                                <h3 class="card-title">Pendapatan Setelah Pajak</h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <td>DPP <small class="text-muted">(Total Sewa ÷ 1,11)</small></td>
                                            <td class="text-right text-end" id="est-dpp"></td>
                                        </tr>
                                        <tr>
                                            <td>PPN <small class="text-muted">(DPP × 11%)</small></td>
                                            <td class="text-right text-end" id="est-ppn"></td>
                                        </tr>
                                        <tr>
                                            <td>PPH <small class="text-muted">(DPP × 2%)</small></td>
                                            <td class="text-right text-end" id="est-pph"></td>
                                        </tr>
                                        <tr class="font-weight-bold fw-bold">
                                            <td>Total Pendapatan Bersih
                                                <small class="text-muted">(Total Sewa − PPN − PPH − Pengeluaran)</small>
                                            </td>
                                            <td class="text-right text-end" id="est-bersih"></td>
                                        </tr>
                                    </tbody>
                                </table>
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

        function formatRupiahDesimal(angka) {
            angka = Number(angka) || 0;
            return 'Rp ' + angka.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
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
                        totalSewa += parseFloat(item.sewa || 0);
                        totalPengeluaran += parseFloat(item.pengeluaran || 0);
                        totalPendapatan += parseFloat(item.pendapatan || 0);
                    });

                    $('#total-sewa').html(formatRupiahDesimal(totalSewa));
                    $('#total-pengeluaran').html(formatRupiahDesimal(totalPengeluaran));
                    $('#total-pendapatan').html(formatRupiahDesimal(totalPendapatan));

                    // Estimasi pendapatan setelah pajak
                    // Total pendapatan = sewa + tambah titik = totalSewa
                    const dpp = totalSewa / 1.11;
                    const ppn = dpp * 0.11;
                    const pph = dpp * 0.02;
                    const bersih = totalSewa - ppn - pph - totalPengeluaran;

                    $('#est-dpp').text(formatRupiahDesimal(dpp));
                    $('#est-ppn').text(formatRupiahDesimal(ppn));
                    $('#est-pph').text(formatRupiahDesimal(pph));
                    $('#est-bersih')
                        .text(formatRupiahDesimal(bersih))
                        .toggleClass('text-danger', bersih < 0);
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
