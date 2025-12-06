@extends('layouts.app')

@section('content')
    <section class="list py-5">
        <div class="container">
            <h2 class="list-title">
                <span>🔥</span> Các giải đấu mới nhất <span>🔥</span>
            </h2>

            {{-- Bộ lọc --}}
            <div class="filter-bar d-flex flex-column align-items-center gap-3 mb-2">
                <form method="GET" action="{{ route('list') }}" class="d-flex flex-column align-items-center gap-3 mb-5">

                    <!-- Tìm kiếm -->
                    <div class="search-box position-relative w-100">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control bg-dark text-white border-secondary pe-5"
                            placeholder="Tìm kiếm giải đấu...">
                        <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-secondary"></i>
                    </div>

                    <!-- Thể loại & Bộ môn -->
                    <div class="d-flex gap-3">
                        <select name="category" id="category" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Chọn thể loại --</option>
                            <option value="sport" {{ request('category') == 'sport' ? 'selected' : '' }}>Thể thao</option>
                            <option value="e-sport" {{ request('category') == 'e-sport' ? 'selected' : '' }}>E-Sport
                            </option>
                        </select>

                        <select name="game_name" id="game_name" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Chọn bộ môn --</option>
                        </select>
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
                    <p class="text-center text-muted">Chưa có giải đấu nào.</p>
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
        const sportGames = ["Bóng đá", "Bóng rổ", "Cầu lông", "Bóng chuyền", "Bơi lội", "Chạy bộ"];
        const eSportGames = ["Liên Minh Huyền Thoại", "Valorant", "CS2", "PUBG Mobile", "Tốc Chiến", "Dota 2"];

        const categorySelect = document.getElementById('category');
        const gameSelect = document.getElementById('game_name');
        const searchInput = document.querySelector('.search-box input');
        const cards = document.querySelectorAll('.list-card');

        // Thay đổi bộ môn theo thể loại
        categorySelect.addEventListener('change', function() {
            gameSelect.innerHTML = '<option value="">-- Chọn bộ môn --</option>';
            let games = [];
            if (this.value === 'sport') games = sportGames;
            if (this.value === 'e-sport') games = eSportGames;

            games.forEach(game => {
                const option = document.createElement('option');
                option.value = game;
                option.textContent = game;
                gameSelect.appendChild(option);
            });

            filterCards();
        });

        // Khi chọn bộ môn
        gameSelect.addEventListener('change', filterCards);
        // Khi gõ tìm kiếm
        searchInput.addEventListener('input', filterCards);

        function filterCards() {
            const category = categorySelect.value.toLowerCase();
            const game = gameSelect.value.toLowerCase();
            const search = searchInput.value.toLowerCase();

            let visibleCount = 0;

            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const fullGameText = card.querySelector('p').textContent.toLowerCase();
                // Lấy TÊN BỘ MÔN CHUẨN bằng cách loại bỏ "bộ môn: "
                const gameNameOnly = fullGameText.replace('bộ môn:', '').trim();

                // Chuyển mảng eSportGames sang chữ thường để kiểm tra so sánh
                const lowerCaseESportGames = eSportGames.map(g => g.toLowerCase());
                const isESport = lowerCaseESportGames.includes(gameNameOnly);

                // Logic kiểm tra thể loại đúng
                let matchCategory = !category ||
                    (category === 'sport' && !isESport) ||
                    (category === 'e-sport' && isESport);

                // Dùng gameNameOnly để lọc chính xác
                const matchGame = !game || gameNameOnly.includes(game);
                const matchSearch = !search || title.includes(search);

                if (matchCategory && matchGame && matchSearch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            let emptyMsg = document.querySelector('.empty-message');
            if (!emptyMsg) {
                emptyMsg = document.createElement('p');
                emptyMsg.className = 'empty-message text-center text-muted mt-4';
                emptyMsg.textContent = 'Chưa có giải đấu nào.';
                document.querySelector('.list-container').appendChild(emptyMsg);
            }
            emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    </script>
@endsection
