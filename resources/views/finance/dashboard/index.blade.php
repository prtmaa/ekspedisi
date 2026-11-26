@extends('layouts.master')

@section('tittle')
    Data Pengiriman
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"> <a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Finance</li>
@endsection

@section('content')
    <div class="container-fluid">

        <div class="row">

            <section class="col-lg-12 connectedSortable">

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">

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
                                            <th>Bon Sementara</th>
                                            <th>Pengeluaran</th>
                                            <th>Sisa</th>
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
                        url: '{{ route('dashboard.data') }}',
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
                            data: 'bon_sementara'
                        },
                        {
                            data: 'pengeluaran',
                        },
                        {
                            data: 'sisa'
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
                        $('#modal-form [name=sewa]').val(response.sewa);
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
        </script>
    @endpush
