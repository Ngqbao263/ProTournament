@extends('layouts.app')

@section('content')
    {{-- Banner --}}
    <section class="hero-section position-relative text-white">
        <!-- Ảnh nền -->
        <div class="hero-bg"></div>

        <div class="container text-center position-relative z-1 py-5">
            <!-- Logo nhỏ -->
            <img src="{{ asset('home/img/logo.png') }}" alt="Logo" class="mb-3" style="width: 200px;">

            <!-- Tiêu đề -->
            <h1 class="fw-bold mb-2">Tổ chức giải đấu dễ dàng</h1>
            <h2 class="fw-bold mb-4">Kết nối đam mê, lan tỏa tinh thần thể thao!</h2>

            <!-- Các nút hành động hiện đại -->
            <div class="action-buttons d-flex flex-wrap justify-content-center gap-3 mb-5">
                <a href="{{ route('tournaments.create') }}" class="btn-modern btn-create">
                    <i class="bi bi-trophy me-2"></i> Tạo giải đấu
                </a>
                <a href="{{ route('list') }}" class="btn-modern btn-list">
                    <i class="bi bi-collection me-2"></i> Tất cả giải đấu
                </a>
                {{-- <a href="#" class="btn-modern btn-search">
                    <i class="bi bi-newspaper me-2"></i> Tin tức
                </a> --}}
            </div>

            <!-- Các số thống kê -->
            <div class="row text-center mt-4">
                <div class="col-6 col-md-3">
                    <i class="bi bi-trophy-fill fs-1 text-warning"></i>
                    <h3 class="fw-bold mt-2 counter" data-target="57008">0</h3>
                    <p>Giải đấu</p>
                </div>
                <div class="col-6 col-md-3">
                    <i class="bi bi-people-fill fs-1 text-primary"></i>
                    <h3 class="fw-bold mt-2 counter" data-target="308939">0</h3>
                    <p>Người dùng hoạt động</p>
                </div>
                <div class="col-6 col-md-3">
                    <i class="bi bi-person-badge-fill fs-1 text-success"></i>
                    <h3 class="fw-bold mt-2 counter" data-target="1627095">0</h3>
                    <p>Vận động viên</p>
                </div>
                <div class="col-6 col-md-3">
                    <i class="bi bi-lightning-charge-fill fs-1 text-danger"></i>
                    <h3 class="fw-bold mt-2 counter" data-target="1892084">0</h3>
                    <p>Trận đấu</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Giới thiệu trang web --}}
    <section class="introduce-home d-flex align-items-center justify-content-center flex-wrap py-5 px-4">
        <div class="introduce-home-image position-relative">
            <img src="{{ asset('home/img/introduce.png') }}" class="img-fluid rounded shadow-lg" alt="Ảnh dịch vụ">
        </div>

        <div class="introduce-home-content bg-dark text-white p-5 rounded shadow-lg position-relative">
            <h2 class="fw-bold mb-3">Dịch vụ chúng tôi cung cấp</h2>
            <p class="mb-3">
                Website của chúng tôi cung cấp một nền tảng quản lý giải đấu mạnh mẽ và trực quan, giúp bạn tổ chức và theo
                dõi mọi sự kiện thể thao một cách dễ dàng và hiệu quả.
            </p>
            <p class="mb-4">
                Hãy để chúng tôi đơn giản hóa quy trình tổ chức, giúp bạn tập trung vào những trận đấu hấp dẫn!
            </p>
            <a href="#" class="btn btn-light">Tìm hiểu thêm</a>
        </div>
    </section>

    {{-- Giới thiệu các thể thức --}}
    <section class="ml-formats-section text-center text-white py-5">
        <div class="container">
            <!-- Tiêu đề -->
            <h2 class="fw-bold mb-3">Các thể thức thi đấu</h2>
            <p class="text-light-emphasis mb-5">
                Pro Tournament giúp người dùng tạo các giải đấu linh hoạt, mô phỏng nhiều thể thức nổi tiếng như
                Champions League, World Cup, NBA, ATP Cup và hơn thế nữa!
            </p>
            <!-- Icon thể thức -->
            <div class="row justify-content-center g-4">
                <div class="col-6 col-md-2">
                    <i class="bi bi-diagram-3 fs-1"></i>
                    <p class="fw-semibold mt-2">Loại trực tiếp</p>
                </div>
                <div class="col-6 col-md-2">
                    <i class="bi bi-grid-3x3-gap fs-1"></i>
                    <p class="fw-semibold mt-2">Đấu vòng tròn</p>
                </div>
                <div class="col-6 col-md-2">
                    <i class="bi bi-table fs-1"></i>
                    <p class="fw-semibold mt-2">Chia bảng đấu</p>
                </div>
                <div class="col-6 col-md-2">
                    <i class="bi bi-diagram-2 fs-1"></i>
                    <p class="fw-semibold mt-2">Nhánh thắng - Nhánh thua</p>
                </div>
                <div class="col-6 col-md-2">
                    <i class="bi bi-diagram-3-fill fs-1"></i>
                    <p class="fw-semibold mt-2">Thể thức hỗn hợp</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Danh sách giải đấu --}}
    <section class="list">
        <h2 class="list-title">
            <span>🔥</span> Các giải đấu mới nhất <span>🔥</span>
        </h2>
        <div class="list-container">
            @forelse ($tournaments as $tournament)
                <div class="list-card">
                    {{-- Thêm class position-relative và d-block vào thẻ a bao quanh ảnh --}}
                    <a href="{{ route('tournament.show', $tournament->id) }}" class="d-block position-relative">
                        <img src="{{ Str::startsWith($tournament->thumbnail, 'home/') ? asset($tournament->thumbnail) : asset('storage/' . $tournament->thumbnail) }}"
                            alt="{{ $tournament->name }}" style="width: 100%; display: block;" /> {{-- Đảm bảo ảnh full width --}}

                        {{-- CODE COPY TỪ PHẦN CŨ SANG: Badge trạng thái --}}
                        <div class="position-absolute top-0 end-0 m-2">
                            @if ($tournament->status == 'open')
                                <span class="badge bg-success">Mở đăng ký</span>
                            @elseif($tournament->status == 'started')
                                <span class="badge bg-warning text-dark">Đang diễn ra</span>
                            @elseif($tournament->status == 'finished')
                                <span class="badge bg-secondary">Kết thúc</span>
                            @endif
                        </div>
                    </a>

                    <div class="list-info">
                        <a href="{{ route('tournament.show', $tournament->id) }}" style="text-decoration: none">
                            <h3>{{ $tournament->name }}</h3>
                        </a>
                        <p>Bộ môn: {{ $tournament->game_name }}</p>
                        <p class="date">
                            Ngày bắt đầu:
                            {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center">Chưa có giải đấu nào.</p>
            @endforelse
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('list') }}" class="btn btn-view-more">Xem thêm ›</a>
        </div>
    </section>


    {{-- Tin tưởng bởi --}}
    {{-- <section class="trusted-section py-5">
        <div class="container text-center">
            <h4 class="fw-bold mb-4 text-uppercase text-white">Được tin tưởng bởi</h4>

            <div class="row justify-content-center align-items-center g-4 trusted-logos">
                <div class="col-4 col-md-2"><img src="{{ asset('home/img/banner1.png') }}" alt="Ubisoft">
                </div>
                <div class="col-4 col-md-2"><img src="{{ asset('home/img/banner1.png') }}" alt="Red Bull"></div>
                <div class="col-4 col-md-2"><img src="{{ asset('home/img/banner1.png') }}" alt="Riot Games"></div>
                <div class="col-4 col-md-2"><img src="{{ asset('home/img/banner1.png') }}" alt="Microsoft">
                </div>
                <div class="col-4 col-md-2"><img src="{{ asset('home/img/banner1.png') }}" alt="Logitech">
                </div>
                <div class="col-4 col-md-2"><img src="{{ asset('home/img/banner1.png') }}" alt="PGL"></div>
            </div>
        </div>
    </section> --}}

    <!-- Font Awesome (icon) -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    {{-- Thống kê --}}
    <script>
        document.querySelectorAll('.counter').forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / 50;
                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 20);
                } else counter.innerText = target.toLocaleString();
            };
            updateCount();
        });
    </script>
@endsection
