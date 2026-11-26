@extends('layouts.master')

@section('tittle')
    Data Pengiriman
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"> <a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pengiriman</li>
@endsection

@section('content')
    <div class="container-fluid">

        <div class="row">

            <section class="col-lg-12 connectedSortable">

                <div class="row">
                    <div class="col-md-12">

                        <div class="card">
                            <div class="card-body">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tbody>
                                            <tr>
                                                <td>Tanggal Pengiriman</td>
                                                <td>:</td>
                                                <td>{{ formatTanggalIndo($pengiriman->tanggal) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Tipe Pengiriman</td>
                                                <td>:</td>
                                                <td>{{ $pengiriman->tipe->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td>Tujuan</td>
                                                <td>:</td>
                                                <td>{{ $pengiriman->tujuann->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td>Jenis Muatan</td>
                                                <td>:</td>
                                                <td>{{ $pengiriman->jenis }}</td>
                                            </tr>
                                            <tr>
                                                <td>Driver</td>
                                                <td>:</td>
                                                <td>{{ $pengiriman->driver->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td>Sewa</td>
                                                <td>:</td>
                                                <td>{{ formatRupiah($pengiriman->sewa) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Bon Sementara</td>
                                                <td>:</td>
                                                <td>{{ formatRupiah($pengiriman->bon_sementara) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Sisa</td>
                                                <td>:</td>
                                                <td id="sisa">{{ formatRupiah($sisa) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">

                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <div class="btn-group">
                                        <button onclick="addForm('{{ route('pengeluaran.store', $pengiriman->id) }}')"
                                            class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i> Tambah
                                            Data</button>
                                        <a href="{{ route('pengiriman.export', $pengiriman->id) }}"
                                            class="btn btn-sm btn-success"><i class="fa fa-file-excel"></i> Excel</a>
                                    </div>

                                    <a href="{{ url('pengiriman') }}" class="btn btn-sm btn-danger"><i
                                            class="fa fa-back"></i>Kembali</a>
                                </div>

                            </div>

                            <div class="card-body table-responsive">
                                <form action="" class="form-produk" method="post">
                                    @csrf
                                    <table class="table table-striped table-bordered pengeluaran-table">
                                        <thead>
                                            <th style="width: 20px;">No</th>
                                            <th>Jenis Pengeluaran</th>
                                            <th>Nominal</th>
                                            <th>Keterangan</th>
                                            <th style="width: 220px;">Aksi</th>
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
        @include('pengeluaran.modal')
        @include('pengeluaran.form')
    @endsection

    @push('js')
        <script>
            let table;
            $(function() {
                table = $('.pengeluaran-table').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    autoWidth: false,
                    responsive: true,
                    paging: false,
                    searching: false,
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
                        url: '{{ route('pengeluaran.data', $pengiriman->id) }}',
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            searchable: false
                        },
                        {
                            data: 'jenis',
                        },
                        {
                            data: 'nominal',
                        },
                        {
                            data: 'keterangan',
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
                                $('#sisa').text(response.sisa_formatted);

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

            $('#modal-form-edit').validator().on('submit', function(e) {
                if (!e.preventDefault()) {
                    $.ajax({
                            enctype: 'multipart/form-data',
                            url: $('#modal-form-edit form').attr('action'),
                            type: $('#modal-form-edit form').attr('method'),
                            data: new FormData($('#modal-form-edit form')[0]),
                            async: false,
                            processData: false,
                            contentType: false
                        })
                        .done((response) => {
                            $('#modal-form-edit').modal('hide'); // tutup modal
                            table.ajax.reload(); // refresh datatable
                            $('#sisa').text(response.sisa_formatted);

                            Swal.fire({
                                icon: 'success',
                                title: 'Data berhasil diperbarui',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        })
                        .fail((errors) => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Data gagal diperbarui',
                            });
                        });
                }
            });


            function addForm(url) {
                $('#modal-form').modal({
                    backdrop: 'static',
                    keyboard: false
                }).modal('show');
                $('#modal-form .modal-title').text('Tambah Data');

                $('#modal-form form')[0].reset();
                $('#modal-form form').attr('action', url);
                $('#modal-form [name=_method]').val('post');
                $('#modal-form [name=nama_cabang]').focus();
            }

            function editForm(fetchUrl, updateUrl) {
                $('#modal-form-edit').modal({
                    backdrop: 'static',
                    keyboard: false
                }).modal('show');
                $('#modal-form-edit .modal-title').text('Edit Data');
                $('#modal-form-edit form')[0].reset();

                // URL untuk submit data (PUT)
                $('#modal-form-edit form').attr('action', updateUrl);
                $('#modal-form-edit [name=_method]').val('put');

                // Ambil data lama (GET)
                $.get(fetchUrl)
                    .done((response) => {
                        $('#modal-form-edit [name=jenis_pengeluaran]').val(response.jenis_pengeluaran_id);
                        $('#modal-form-edit [name=nominal]').val(response.nominal);
                        $('#modal-form-edit [name=keterangan]').val(response.keterangan);
                    })
                    .fail((errors) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Data gagal ditampilkan',
                        });
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
                                $('#sisa').text(response.sisa_formatted);
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

            flatpickr(".tgl_pengiriman", {
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
        </script>
    @endpush
