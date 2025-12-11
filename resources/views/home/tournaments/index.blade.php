@extends('layouts.app')

@section('content')
    <section class="list py-5">
        <div class="container">
            <h2 class="list-title">
                <span>🔥</span> Các giải đấu mới nhất <span>🔥</span>
            </h2>

            {{-- Bộ lọc --}}
            <div class="filter-bar d-flex flex-column align-items-center gap-3 mb-4">
                <form method="GET" action="{{ route('list') }}" class="d-flex flex-column align-items-center w-100"
                    style="max-width: 600px;">

                    <div class="search-box position-relative w-100 mb-3">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control bg-dark text-white border-secondary pe-5 py-2"
                            placeholder="Tìm kiếm giải đấu...">
                        <button type="submit"
                            class="btn position-absolute top-50 end-0 translate-middle-y text-secondary border-0 bg-transparent">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>

                    <div class="row g-1 w-100">
                        <div class="col-6">
                            <select name="category" id="category"
                                class="form-select bg-dark text-white border-secondary w-100" onchange="this.form.submit()">
                                <option value="">-- Thể loại --</option>
                                <option value="sport" {{ request('category') == 'sport' ? 'selected' : '' }}>Thể thao
                                </option>
                                <option value="e-sport" {{ request('category') == 'e-sport' ? 'selected' : '' }}>E-Sport
                                </option>
                            </select>
                        </div>

                        <div class="col-6">
                            <select name="game_name" id="game_name"
                                class="form-select bg-dark text-white border-secondary w-100" onchange="this.form.submit()">
                                <option value="">-- Bộ môn --</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>



            {{-- Danh sách card --}}
            <div class="list-container">
                @forelse ($tournaments as $tournament)
                    <div class="list-card">
                        <a href="{{ route('tournament.show', $tournament->id) }}">
                            <img src="{{ Str::startsWith($tournament->thumbnail, 'home/')
                                ? asset($tournament->thumbnail)
                                : asset('storage/' . $tournament->thumbnail) }}"
                                alt="{{ $tournament->name }}">
                        </a>

                        <div class="list-info">
                            <a href="{{ route('tournament.show', $tournament->id) }}" style="text-decoration: none;">
                                <h3>{{ $tournament->name }}</h3>
                            </a>
                            <p>Bộ môn: {{ $tournament->game_name }}</p>
                            <p class="date">
                                Ngày bắt đầu: {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-secondary">Chưa có giải đấu nào.</p>
                @endforelse
            </div>

            @if ($tournaments->total() > 0)
                <div class="d-flex justify-content-center mt-4">
                    <div class="custom-pagination">
                        {{ $tournaments->onEachSide(1)->links('vendor.pagination.esport') }}
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Lọc bộ môn theo thể loại --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sportGames = ["Bóng đá", "Bóng rổ", "Cầu lông", "Bóng chuyền", "Bơi lội", "Chạy bộ"];
            const eSportGames = ["Liên Minh Huyền Thoại", "Valorant", "CS2", "PUBG Mobile", "Tốc Chiến", "Dota 2"];

            const categorySelect = document.getElementById('category');
            const gameSelect = document.getElementById('game_name');

            // Lấy giá trị đang được chọn từ Server (để khi load lại trang nó không bị mất)
            const currentCategory = "{{ request('category') }}";
            const currentGame = "{{ request('game_name') }}";

            // Hàm điền options cho game select
            function populateGames(category) {
                // Xóa cũ giữ lại option đầu
                gameSelect.innerHTML = '<option value="">-- Bộ môn --</option>';

                let games = [];
                if (category === 'sport') games = sportGames;
                if (category === 'e-sport') games = eSportGames;

                games.forEach(game => {
                    const option = document.createElement('option');
                    option.value = game;
                    option.textContent = game;
                    // Nếu game này đang được chọn từ trước (do server trả về) thì selected nó
                    if (game === currentGame) {
                        option.selected = true;
                    }
                    gameSelect.appendChild(option);
                });
            }

            // 1. Chạy ngay khi load trang để điền lại option nếu đang filter
            populateGames(currentCategory);

            // 2. Sự kiện khi người dùng thay đổi thể loại
            // Lưu ý: Vì select có onchange="submit", trang sẽ reload ngay lập tức.
            // Nhưng ta vẫn cần sự kiện này để trải nghiệm mượt hơn hoặc nếu bỏ auto-submit.
            categorySelect.addEventListener('change', function() {
                // Reset game về rỗng khi đổi thể loại
                gameSelect.value = "";
            });
        });
    </script>
@endsection
