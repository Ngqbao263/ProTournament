<header class="main-header bg-success sticky-top py-2">
    <div class="container">
        <div class="row align-items-center">
            <!-- Logo -->
            <div class="col-6 col-lg-4 d-flex align-items-center logo">
                <a href="{{ route('home') }}" class="d-flex align-items-center text-white text-decoration-none fw-bold">
                    <img src="{{ asset('home/img/logo.png') }}" class="me-2 logo-img" alt="Logo">
                </a>
            </div>

            <!-- Menu + User -->
            <div class="col-6 col-lg-8 d-flex align-items-center justify-content-end">
                <!-- Menu desktop -->
                <nav class="nav-menu d-none d-lg-flex align-items-center me-4">
                    <ul class="nav mb-0">
                        <li class="nav-item"><a href="{{ route('home') }}" class="nav-link text-white">Trang chủ</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown">Giải
                                đấu</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('list') }}">Tất cả giải
                                        đấu</a></li>
                                <li><a class="dropdown-item" href="{{ route('tournaments.create') }}">Tạo giải đấu</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('tournaments.my') }}">Giải đấu của tôi</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item"><a href="#" class="nav-link text-white">Blog</a></li>
                        {{-- <li class="nav-item"><a href="#" class="nav-link text-white">Mua sắm</a></li> --}}
                    </ul>
                </nav>

                <!-- User (logic Laravel) -->
                @guest
                    <div class="d-none d-lg-flex align-items-center">
                        <a href="{{ route('login') }}" class="btn btn-outline-light me-2">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="btn btn-light text-success fw-semibold">Đăng ký</a>
                    </div>
                @else
                    <div class="dropdown d-none d-lg-flex align-items-center">
                        <i class="bi bi-person-circle text-white fs-5 me-1"></i>
                        <a href="#" class="dropdown-toggle text-white text-decoration-none fw-semibold"
                            data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Tài khoản</a></li>

                            @if (Auth::user()->role === 'admin')
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Quản trị</a></li>
                            @endif

                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Đăng xuất
                                </a>
                            </li>
                        </ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                @endguest

                <!-- Nút hamburger -->
                <button class="navbar-toggler d-lg-none border-0 text-white ms-3" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                    <i class="bi bi-list fs-2"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Offcanvas menu (mobile) -->
<div class="offcanvas offcanvas-start text-white bg-success" tabindex="-1" id="mobileNav">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Pro Tournament</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        <ul class="list-unstyled mb-4">
            <li class="mb-2"><a href="{{ route('home') }}" class="text-white text-decoration-none">Trang chủ</a></li>
            <li class="mb-2"><a href="{{ route('list') }}" class="text-white text-decoration-none">Tất cả giải
                    đấu</a></li>
            <li class="mb-2"><a href="{{ route('tournaments.create') }}" class="text-white text-decoration-none">Tạo
                    giải đấu</a></li>
            <li class="mb-2"><a href="{{ route('tournaments.my') }}" class="text-white text-decoration-none">Giải đấu
                    của tôi</a></li>
        </ul>
        <hr class="border-light">

        @guest
            <a href="{{ route('login') }}" class="btn btn-outline-light w-100 mb-2">Đăng nhập</a>
            <a href="{{ route('register') }}" class="btn btn-light text-success w-100 fw-semibold">Đăng ký</a>
        @else
            <p class="fw-semibold mb-2"><i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}</p>

            @if (Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="text-white text-decoration-none d-block mb-2">Quản trị</a>
            @endif

            <a href="#" class="text-white text-decoration-none d-block"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Đăng xuất
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        @endguest
    </div>
</div>
