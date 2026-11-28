@extends('layouts.master')

@section('tittle')
    Data Pengiriman
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"> <a href="{{ url('/') }}">Dashboard</a></li>
@endsection

@section('content')
    <div class="container-fluid">

        <div class="row">

            <section class="col-lg-12 connectedSortable">

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">

                                <div class="d-flex flex-wrap justify-content-between align-items-center">

                                    <div class="btn-group">
                                        <button onclick="addForm('{{ route('pengiriman.store') }}')"
                                            class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i> Tambah
                                        </button>
                                    </div>

                                    <div class="d-inline-flex gap-2">
                                        <input type="text" id="bulan" class="form-control" placeholder="Pilih Bulan"
                                            style="width: 150px">

                                        <button id="btn-export" class="btn btn-success btn-sm">
                                            <i class="fa fa-file-excel"></i> Export
                                        </button>
                                    </div>
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
                                            <th>Driver</th>
                                            <th>Tujuan</th>
                                            <th>Muatan</th>
                                            <th>Sewa</th>
                                            <th>Tambah Titik</th>
                                            <th>Bon Sementara</th>
                                            <th>Pengeluaran</th>
                                            <th>Sisa</th>
                                            <th style="width: 150px;">Aksi</th>
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

        @include('pengiriman.form')
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
                        url: '{{ route('pengiriman.data') }}',
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
                            data: 'driver_nama'
                        },
                        {
                            data: 'tujuan'
                        },
                        {
                            data: 'jenis'
                        },
                        {
                            data: 'sewa'
                        },
                        {
                            data: 'titik'
                        },
                        {
                            data: 'bon_sementara'
                        },
                        {
                            data: 'pengeluaran',
                        },
                        {
                            data: 'sisa'
                        },

                        {
                            data: 'aksi',
                            "searchable": false,
                            "orderable": false
                        },
                    ]
                });



                $('#modal-form').validator().on('submit', function(e) {
                    if (!e.preventDefault()) {
                        $.ajax({
                                enctype: 'multipart/form-data',
                                url: $('#modal-form form').attr('action'),
                                type: $('#modal-form form').attr('method'),
                                data: new FormData($('#modal-form form')[0]),
                                async: false,
                                processData: false,
                                contentType: false
                            })
                            .done((response) => {
                                $('#modal-form').modal('hide');
                                table.ajax.reload();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Data berhasil disimpan',
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                            })
                            .fail((errors) => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Data gagal disimpan',
                                })
                            });
                    }
                })

            });

            function addForm(url) {
                $('#modal-form').modal({
                    backdrop: 'static',
                    keyboard: false
                }).modal('show');
                $('#modal-form .modal-title').text('Tambah Data');

                $('#modal-form form')[0].reset();
                hitungTotal();
                $('#modal-form form').attr('action', url);
                $('#modal-form [name=_method]').val('post');
                $('#modal-form [name=bs]').focus();
            }

            function editForm(url) {
                $('#modal-form').modal({
                    backdrop: 'static',
                    keyboard: false
                }).modal('show');
                $('#modal-form .modal-title').text('Edit Data');
                $('#modal-form form')[0].reset();
                $('#modal-form form').attr('action', url);
                $('#modal-form [name=_method]').val('put');
                $('#modal-form [name=bs]').focus();

                $.get(url)
                    .done((response) => {
                        $('#modal-form [name=tanggal]').val(response.tanggal);
                        $('#modal-form [name=tipe]').val(response.tipe_pengiriman);
                        $('#modal-form [name=tujuan]').val(response.tujuan);
                        $('#modal-form [name=jenis]').val(response.jenis);
                        $('#modal-form [name=driver_id]').val(response.driver_id);
                        $('#modal-form [name=bs]').val(response.bon_sementara);
                        $('#modal-form [name=sewa]').val(formatRupiah(Math.round(response.sewa || 0)));
                        $('#modal-form [name=titik]').val(formatRupiah(Math.round(response.titik || 0)));
                        hitungTotal();
                    })
                    .fail((errors) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Data gagal ditampilkan',
                        })
                    });
            }

            function deleteData(url) {
                Swal.fire({
                    title: 'Yakin?',
                    text: "Data akan dihapus",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(url, {
                                '_token': $('[name=csrf-token]').attr('content'),
                                '_method': 'delete'
                            })
                            .done((response) => {
                                table.ajax.reload();
                                $('.alertdelete').fadeIn();

                                setTimeout(() => {
                                    $('.alertdelete').fadeOut();
                                }, 3000);
                            })
                            .fail((errors) => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Data gagal dihapus',
                                })
                            });
                    }
                })
            }

            flatpickr(".tanggal", {
                dateFormat: "Y-m-d",
                defaultDate: "today"
            });

            const rupiah = document.getElementById('bs');

            rupiah.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, ''); // Hanya angka
                if (value) {
                    // Format angka dengan titik ribuan
                    value = new Intl.NumberFormat('id-ID').format(value);
                    this.value = value;
                } else {
                    this.value = '';
                }
            });

            const sewa = document.getElementById('sewa');

            sewa.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, ''); // Hanya angka
                if (value) {
                    // Format angka dengan titik ribuan
                    value = new Intl.NumberFormat('id-ID').format(value);
                    this.value = value;
                } else {
                    this.value = '';
                }
            });

            const titik = document.getElementById('titik');

            titik.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, ''); // Hanya angka
                if (value) {
                    // Format angka dengan titik ribuan
                    value = new Intl.NumberFormat('id-ID').format(value);
                    this.value = value;
                } else {
                    this.value = '';
                }
            });

            function angka(val) {
                return parseInt(String(val).replace(/\D/g, '')) || 0;
            }

            function hitungTotal() {
                const total = angka($('#sewa').val()) + angka($('#titik').val());
                $('#total').val(total ? formatRupiah(total) : '');
            }

            $('#sewa, #titik').on('input', hitungTotal);
        </script>

        <script>
            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }

            document.getElementById('tujuan').addEventListener('change', function() {
                let selectedOption = this.options[this.selectedIndex];
                let sewa = selectedOption.getAttribute('data-sewa');

                document.getElementById('sewa').value = formatRupiah(sewa ?? 0);
                hitungTotal();
            });
        </script>
        <script>
            flatpickr("#bulan", {
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
                let bulan = $('#bulan').val(); // contoh: 2026-02

                if (!bulan) {
                    alert('Pilih bulan dulu');
                    return;
                }

                window.location.href =
                    "{{ route('pengiriman.export.bulanan') }}" +
                    "?bulan=" + bulan;
            });
        </script>
    @endpush
