@extends('layouts.master')

@section('tittle')
    Data Pengeluaran (Bulanan)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"> <a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rekap</li>
    <li class="breadcrumb-item active">Per Bulan</li>
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
                                        <div class="col-md-4">
                                            <div class="form-group mb-0 d-flex align-items-center">
                                                <label for="bulan" class="col-md-3 me-2 mb-0">Pilih Bulan:</label>
                                                <input type="month" id="bulan" name="bulan" class="form-control"
                                                    value="{{ date('Y-m') }}">
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
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <th style="width: 20px;">No</th>
                                            <th>Jenis Pengeluaran</th>
                                            <th>Total Nominal</th>

                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" style="text-align:center">Total Keseluruhan:</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5>Grafik Pengeluaran per Bulan</h5>
                            </div>
                            <div class="card-body">
                                <div class="">
                                    <canvas id="lineChart" height="100"></canvas>
                                </div>

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
        let table;
        $(function() {
            table = $('.table').DataTable({
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
                ajax: {
                    url: '{{ route('rekap.data') }}',
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'jenis_pengeluaran',
                        render: function(data, type, row) {
                            return `<a href="#" id="btn-detail" data-jenis="${data}">${data}</a>`;
                        },
                        orderable: false
                    },
                    {
                        data: 'total_rupiah',
                        orderable: false
                    }
                ],

                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // Fungsi untuk parsing angka dari format 'Rp 1.000.000' ke angka murni
                    var parseRupiah = function(val) {
                        if (typeof val === 'string') {
                            // Hapus 'Rp', spasi, titik dan koma
                            return parseFloat(val.replace(/Rp\s?|\.|,/g, '')) || 0;
                        }
                        if (typeof val === 'number') {
                            return val;
                        }
                        return 0;
                    };

                    // Hitung total dari seluruh data yang ditampilkan di tabel (bukan hanya halaman)
                    var total = api
                        .column(2, {
                            search: 'applied'
                        }) // kolom index 2: Total Nominal
                        .data()
                        .reduce(function(a, b) {
                            return parseRupiah(a) + parseRupiah(b);
                        }, 0);

                    // Tampilkan total di footer, format angka dengan toLocaleString dan prefix Rp
                    $(api.column(2).footer()).html('Rp ' + total.toLocaleString());
                },

            });


            $('#bulan').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chart;

        function loadChart() {
            fetch('{{ route('rekap.chart-data') }}')
                .then(res => res.json())
                .then(res => {

                    const json = res.data;

                    const bulanLabels = [
                        'Januari', 'Februari', 'Maret', 'April',
                        'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ];

                    const jenisSet = new Set();
                    const dataMap = {};

                    json.forEach(item => {
                        jenisSet.add(item.jenis);

                        if (!dataMap[item.jenis]) {
                            dataMap[item.jenis] = {};
                        }

                        // bulan - 1 agar sesuai index array (0–11)
                        dataMap[item.jenis][item.bulan - 1] = item.total;
                    });

                    const jenisArray = Array.from(jenisSet);

                    const colors = [
                        '#f56954', '#00a65a', '#f39c12', '#00c0ef',
                        '#3c8dbc', '#d81b60', '#605ca8', '#39cccc'
                    ];

                    const datasets = jenisArray.map((jenis, index) => ({
                        label: jenis,
                        data: bulanLabels.map((_, i) => dataMap[jenis][i] || 0),
                        borderColor: colors[index % colors.length],
                        backgroundColor: 'transparent',
                        tension: 0.3
                    }));

                    if (chart) chart.destroy();

                    const ctx = document.getElementById('lineChart').getContext('2d');
                    chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: bulanLabels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Pengeluaran Warehouse Tahun ' + res.tahun
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: value => 'Rp ' + value.toLocaleString('id-ID')
                                    }
                                }
                            }
                        }
                    });
                });
        }

        loadChart();

        $('.table').on('click', '#btn-detail', function(e) {
            e.preventDefault(); // cegah default link reload

            const jenis = $(this).data('jenis');
            const bulan = $('#bulan').val();

            $.get("{{ route('rekap.detailJenis') }}", {
                jenis,
                bulan
            }, function(response) {
                let html = '';

                if (response.length === 0) {
                    html = '<tr><td colspan="3" class="text-center">Data tidak ditemukan</td></tr>';
                } else {
                    response.forEach(item => {
                        html += `<tr>
                    <td>${item.tanggal}</td>
                    <td>Rp ${parseInt(item.nominal).toLocaleString('id-ID')}</td>
                    <td>${item.keterangan || '-'}</td>
                </tr>`;
                    });
                }

                $('#detailBody').html(html);
                $('#modalDetail').modal('show');
            }).fail(function() {
                alert('Gagal mengambil data detail.');
            });
        });

        document.getElementById('btn-export').addEventListener('click', function() {
            const bulan = document.getElementById('bulan').value; // Ambil dari input, bukan tombol
            const url = "{{ route('rekap.export') }}" + (bulan ? '?bulan=' + bulan : '');
            window.open(url, '_blank');
        });
    </script>
@endpush
