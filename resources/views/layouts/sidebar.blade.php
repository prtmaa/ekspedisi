<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Brand / User Panel -->
        <div class="user-panel mt-3 pb-2 mb-3 d-flex align-items-center">
            <div class="image">
                <img src="{{ asset('favicon.png') }}" style="width:30px; height:30px; object-fit:cover;" alt="Logo">
            </div>
            <div class="info ml-2">
                <a href="#" class="d-block font-weight-bold">Ekspedisi WH</a>
            </div>
        </div>


        <!-- Sidebar Menu -->
        <nav class="mt-2 d-flex flex-column">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <a href="{{ url('pengiriman') }}"
                        class="nav-link {{ (request()->is('pengiriman*') ? 'active' : '' || request()->is('pengeluaran*')) ? 'active' : '' }}">
                        <i class="nav-icon fas  fa-shipping-fast"></i>
                        <p>Pengiriman</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('perawatan') }}" class="nav-link {{ request()->is('perawatan*') ? 'active' : '' }}">
                        <i class="nav-icon fas  fa-wrench"></i>
                        <p>Perawatan</p>
                    </a>
                </li>

                   <li
                    class="nav-item {{ request()->is('rekap*') || request()->is('rkptipe*') || request()->is('eksternal*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ request()->is('rekap*') || request()->is('rkptipe*') || request()->is('eksternal*') ? 'active' : ' ?>' }}">
                        <i class="nav-icon fa fa-file-alt"></i>
                        <p>Rekap <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('rekap') }}"
                                class="nav-link {{ request()->is('rekap*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt ml-2"></i>
                                <p>Per Bulan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('rkptipe') }}"
                                class="nav-link {{ request()->is('rkptipe*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tags ml-2"></i>
                                <p>Per Tipe</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('eksternal') }}"
                                class="nav-link {{ request()->is('eksternal*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-list ml-2"></i>
                                <p>Eksternal</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <div class="user-panel mt-1 mb-1 d-flex"></div>

                <li
                    class="nav-item {{ request()->is('dashboard*') || request()->is('lr*') || request()->is('pengeluaranfin*') || request()->is('cost*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ request()->is('dashboard*') || request()->is('lr*') || request()->is('pengeluaranfin*') || request()->is('cost*') ? 'active' : ' ?>' }}">
                        <i class="nav-icon fa fa-money-bill"></i>
                        <p>Finance <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('dashboard') }}"
                                class="nav-link {{ (request()->is('dashboard*') ? 'active' : '' || request()->is('pengeluaranfin*')) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database ml-2"></i>
                                <p>Data</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('lr') }}" class="nav-link {{ request()->is('lr*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-receipt ml-2"></i>
                                <p>LR Ekspedisi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('cost') }}"
                                class="nav-link {{ request()->is('cost*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-invoice-dollar ml-2"></i>
                                <p>Pajak & Depresiasi</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <div class="user-panel mt-1 mb-1 d-flex"></div>

                <li
                    class="nav-item {{ request()->is('driver*') || request()->is('tujuan*') || request()->is('tipe*') || request()->is('jenis*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ request()->is('driver*') || request()->is('tujuan*') || request()->is('tipe*') || request()->is('jenis*') ? 'active' : '' }}">
                        <i class="nav-icon fa fa-cubes"></i>
                        <p>Master <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('driver') }}"
                                class="nav-link {{ request()->is('driver*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-tie ml-2"></i>
                                <p>Driver</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('tujuan') }}"
                                class="nav-link {{ request()->is('tujuan*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-map-marker-alt ml-2"></i>
                                <p>Tujuan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('tipe') }}"
                                class="nav-link {{ request()->is('tipe*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-truck ml-2"></i>
                                <p>Tipe</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('jenis') }}"
                                class="nav-link {{ request()->is('jenis*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-money-bill-wave ml-2"></i>
                                <p>Pengeluaran</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="{{ url('user') }}"
                        class="nav-link {{ (request()->is('user*') ? 'active' : '' || request()->is('user*')) ? 'active' : '' }}">
                        <i class="nav-icon fas  fa-user"></i>
                        <p>User</p>
                    </a>
                </li>

            </ul>
        </nav>

        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
