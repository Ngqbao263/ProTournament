<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>MyLeague - Mô phỏng</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0f1720;
            --card: #0e1620;
            --muted: #94a3b8;
            --accent: #ff6b2d;
        }

        body {
            background: linear-gradient(180deg, var(--bg) 0%, #071019 100%);
            color: #e6eef8;
            font-family: Inter, system-ui, -apple-system, "Helvetica Neue", Arial;
            line-height: 1.5;
        }

        /* Header */
        .main-header {
            background: linear-gradient(to bottom, #1b7c00, #157000);
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .main-header .nav-link {
            color: white;
            margin: 0 10px;
            font-weight: 500;
            transition: 0.2s;
        }

        .main-header .nav-link:hover {
            color: #ffeb3b;
        }

        .main-header .dropdown-menu {
            background-color: #157000;
            border: none;
        }

        .main-header .dropdown-item {
            color: white;
        }

        .main-header .dropdown-item:hover {
            background-color: #0f5a00;
        }

        /* --- Responsive --- */
        @media (max-width: 991px) {
            .main-header .container {
                flex-wrap: wrap;
            }

            .navbar-toggler {
                background: none;
            }

            .mobile-menu a:hover {
                background: #0f5a00;
                text-decoration: none;
            }
        }

        .list {
            text-align: center;
            padding: 60px 20px;
            background-color: #0d0d0d;
            color: white;
        }

        .list-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .list-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .list-card {
            background: linear-gradient(145deg, #1a1a1a, #111);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .list-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .list-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            filter: brightness(0.9);
            transition: filter 0.3s ease;
        }

        .list-card:hover img {
            filter: brightness(1.1);
        }

        .list-info {
            padding: 20px;
            text-align: left;
        }

        .list-info h3 {
            margin: 0 0 8px;
            font-size: 1.2rem;
            color: #fff;
            font-weight: 600;
        }

        .list-info .date {
            font-size: 0.9rem;
            color: #bbb;
            margin-bottom: 8px;
        }

        .list-info p {
            color: #ddd;
            font-size: 0.95rem;
        }

        /* Nút xem thêm */
        .btn-view-more {
            display: inline-block;
            padding: 10px 24px;
            color: #fff;
            background: transparent;
            border: 2px solid #007bff;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-view-more:hover {
            background-color: #007bff;
            color: white;
            box-shadow: 0 0 12px #007bff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .list {
                padding: 40px 15px;
            }

            .list-title {
                font-size: 1.6rem;
            }

            .list-card img {
                height: 160px;
            }
        }

        @media (max-width: 480px) {
            .list-title {
                font-size: 1.3rem;
            }

            .list-card img {
                height: 150px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-6 col-lg-4 d-flex align-items-center">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none fw-bold">
                        <img src="https://via.placeholder.com/40x40" class="me-2" alt="Logo">
                        <span class="fs-5">MyLeague</span>
                    </a>
                </div>

                <!-- Menu + User -->
                <div class="col-6 col-lg-8 d-flex align-items-center justify-content-end">
                    <!-- Menu desktop -->
                    <nav class="nav-menu d-none d-lg-flex align-items-center me-4">
                        <ul class="nav mb-0">
                            <li class="nav-item"><a href="#" class="nav-link text-white">Trang chủ</a></li>
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle text-white"
                                    data-bs-toggle="dropdown">Giải đấu</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Tất cả giải đấu</a></li>
                                    <li><a class="dropdown-item" href="#">Tạo giải đấu</a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a href="#" class="nav-link text-white">Blog</a></li>
                            <li class="nav-item"><a href="#" class="nav-link text-white">Mua sắm</a></li>
                        </ul>
                    </nav>

                    <!-- User desktop -->
                    <div class="dropdown d-none d-lg-block">
                        <i class="bi bi-person-circle"></i>
                        <a href="#" class="dropdown-toggle text-white text-decoration-none fw-semibold"
                            data-bs-toggle="dropdown">
                            Quoc Bao Nguyen
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Tài khoản</a></li>
                            <li><a class="dropdown-item" href="#">Đăng xuất</a></li>
                        </ul>
                    </div>

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
            <h5 class="offcanvas-title">MyLeague</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled mb-4">
                <li class="mb-2"><a href="#" class="text-white text-decoration-none">Trang chủ</a></li>
                <li class="mb-2"><a href="#" class="text-white text-decoration-none">Giải đấu</a></li>
                <li class="mb-2"><a href="#" class="text-white text-decoration-none">Đội thi đấu</a></li>
                <li class="mb-2"><a href="#" class="text-white text-decoration-none">Bảng giá</a></li>
                <li class="mb-2"><a href="#" class="text-white text-decoration-none">Blog</a></li>
                <li class="mb-2"><a href="#" class="text-white text-decoration-none">Mua sắm</a></li>
            </ul>
            <hr class="border-light">
            <a href="#" class="text-white text-decoration-none d-block mb-2">Quoc Bao Nguyen</a>
            <a href="#" class="text-white text-decoration-none d-block">Đăng xuất</a>
        </div>
    </div>


    <script>
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('mobileMenu').classList.toggle('d-none');
        });
    </script>


    <!-- Mobile offcanvas -->
    <div class="offcanvas offcanvas-start text-white bg-dark" tabindex="-1" id="mobileNav">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">MyLeague</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li class="mb-2"><a href="#" class="text-decoration-none text-white">Trang chủ</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-white">Giải đấu</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-white">Đội</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-white">Bảng giá</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-white">Blog</a></li>
            </ul>
        </div>
    </div>


    {{-- Danh sách giải đấu --}}
    <section class="list">
        <h2 class="list-title">Danh sách các giải đấu</h2>
        <div class="list-container">
            <div class="list-card">
                <img src="{{ asset('home/img/banner1.png') }}" alt="Belgium Tour" />
                <div class="list-info">
                    <h3>Belgium Tour</h3>
                    <p class="date">24th AUGUST</p>
                    <p>Discover the new album live.</p>
                </div>
            </div>

            <div class="list-card">
                <img src="{{ asset('home/img/banner1.png') }}" alt="Barcelona Night" />
                <div class="list-info">
                    <h3>Barcelona Night</h3>
                    <p class="date">27th AUGUST</p>
                    <p>Get up close with the artists.</p>
                </div>
            </div>

            <div class="list-card">
                <img src="{{ asset('home/img/banner1.png') }}" alt="Amsterdam Tour" />
                <div class="list-info">
                    <h3>Amsterdam Tour</h3>
                    <p class="date">9th SEPTEMBER</p>
                    <p>Tour Grand Final.</p>
                </div>
            </div>

            <div class="list-card">
                <img src="{{ asset('home/img/banner1.png') }}" alt="Paris Cup" />
                <div class="list-info">
                    <h3>Paris Cup</h3>
                    <p class="date">15th OCTOBER</p>
                    <p>The final showdown.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('list') }}" class="btn btn-view-more">Xem thêm ›</a>
        </div>
    </section>



    <!-- Footer -->
    <footer class="site-footer">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-3 mb-md-0">
                <img src="https://via.placeholder.com/40" alt="" class="me-2 rounded-circle" />
                <div>
                    <div class="fw-bold">MyLeague</div>
                    <div class="small text-muted">Tạo & quản lý giải đấu</div>
                </div>
            </div>

            <div class="small text-muted text-center">
                © <span id="yr"></span> MyLeague. All rights reserved.
            </div>

            <div class="d-flex gap-3">
                <a href="#" class="text-muted text-decoration-none"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-muted text-decoration-none"><i class="bi bi-youtube"></i></a>
                <a href="#" class="text-muted text-decoration-none"><i class="bi bi-instagram"></i></a>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('yr').textContent = new Date().getFullYear();
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
