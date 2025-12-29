@extends('layouts.app')

@section('content')
    <div class="container py-5 text-white" style="background-color: #121212; border-radius: 10px;">
        <!-- Tiêu đề giải đấu -->
        <div class="text-center mb-3">
            <h1 class="detail-title fw-bold mb-2">{{ $tournament->name }}</h1>
            @php
                $statusLabel = match ($tournament->status) {
                    'open' => 'Mở đăng ký',
                    'started' => 'Đang diễn ra',
                    'finished' => 'Kết thúc',
                    default => 'Không xác định',
                };

                $statusClass = match ($tournament->status) {
                    'open' => 'bg-success text-white',
                    'started' => 'bg-warning text-dark',
                    'finished' => 'bg-secondary text-white',
                    'cancelled' => 'bg-danger',
                    default => 'bg-secondary',
                };
            @endphp

            {{-- 3. Hiển thị ra --}}
            <span class="badge px-3 py-2 fs-6 {{ $statusClass }}">
                {{ $statusLabel }}
            </span>

            @if ($tournament->thumbnail)
                <div class="mt-3">
                    @if (Str::startsWith($tournament->thumbnail, 'thumbnail_tournament/'))
                        <img src="{{ asset('storage/' . $tournament->thumbnail) }}" alt="Thumbnail"
                            class="img-fluid rounded shadow" style="max-height: 300px; object-fit: cover;">
                    @else
                        <img src="{{ asset($tournament->thumbnail) }}" alt="Thumbnail" class="img-fluid rounded shadow"
                            style="max-height: 300px; object-fit: cover;">
                    @endif
                </div>
            @endif
        </div>

        <!-- Thông tin chi tiết -->
        <div class="text-center mb-3">
            <p class="mb-1"><i class="bi bi-controller me-2"></i><strong>Bộ môn:</strong>
                {{ $tournament->game_name }}</p>
            <p class="mb-1"><i class="bi bi-people-fill me-2"></i><strong>Tối đa:</strong>
                {{ $tournament->max_player }}
                {{ isset($tournament->mode) && $tournament->mode == 'team' ? 'Đội' : 'Người chơi' }}</p>
            <p class=""><i class="bi bi-clipboard2-check me-2"></i><strong>Thể thức:</strong>
                @if ($tournament->type == 'single_elimination')
                    Loại trực tiếp
                @elseif($tournament->type == 'double_elimination')
                    Nhánh thắng nhánh thua
                @else
                    Vòng tròn
                @endif
            </p>

            <div class="d-flex justify-content-center gap-2">
                @if ($tournament->creator_id == auth()->id() && $tournament->status == 'open')
                    <form action="{{ route('tournament.start', $tournament->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-success px-4"><i class="bi bi-play-fill me-2"></i>Bắt đầu giải</button>
                    </form>
                @endif

                {{-- Nút Danh sách người chơi --}}
                <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">

                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary px-4 d-flex align-items-center">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng ký tham gia
                        </a>
                    @endguest

                    @auth
                        @if (
                            $tournament->creator_id != auth()->id() &&
                                $tournament->status == 'open' &&
                                $tournament->players->where('status', 'approved')->count() < $tournament->max_player)
                            {{-- Đăng ký ĐỘI --}}
                            @if ($tournament->mode == 'team')
                                <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal"
                                    data-bs-target="#joinTeamModal">
                                    Đăng ký Đội
                                </button>

                                {{-- Đăng ký CÁ NHÂN --}}
                            @else
                                <form action="{{ route('tournament.join', $tournament->id) }}" method="POST"
                                    class="ajax-join-form">
                                    @csrf
                                    <button class="btn btn-primary px-4" style="height: 40px">
                                        Đăng ký tham gia
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endauth
                    <button type="button" class="btn btn-outline-light px-4" data-bs-toggle="modal"
                        data-bs-target="#playerModal">
                        <i class="bi bi-people-fill me-2"></i>
                        {{ isset($tournament->mode) && $tournament->mode == 'team' ? 'Danh sách Đội' : 'Danh sách Người chơi' }}
                    </button>
                </div>

            </div>
        </div>

        <!-- Mô tả -->
        <div class="mb-4">
            <ul class="nav nav-pills" id="tournamentTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="desc-tab" data-bs-toggle="pill" data-bs-target="#desc-content"
                        type="button" role="tab">
                        <i class="bi bi-info-circle me-2"></i>Mô tả giải đấu
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bracket-tab" data-bs-toggle="pill" data-bs-target="#bracket-content"
                        type="button" role="tab">
                        <i class="bi bi-diagram-3 me-2"></i>Sơ đồ thi đấu
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="schedule-tab" data-bs-toggle="pill" data-bs-target="#schedule-content"
                        type="button" role="tab">
                        <i class="bi bi-calendar-event me-2"></i>Lịch thi đấu
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ranking-tab" data-bs-toggle="pill" data-bs-target="#ranking-content"
                        type="button" role="tab">
                        <i class="bi bi-trophy me-2"></i>Bảng xếp hạng
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="tournamentTabContent">
            {{-- Tab Mô tả --}}
            <div class="tab-pane fade show active" id="desc-content" role="tabpanel">
                @include('home.tournaments.tab.tab-desc')
            </div>

            {{-- Sơ đồ thi đấu --}}
            <div class="tab-pane fade" id="bracket-content" role="tabpanel">
                @include('home.tournaments.tab.tab-bracket')
            </div>

            {{-- Tab Lịch thi đấu --}}
            <div class="tab-pane fade" id="schedule-content" role="tabpanel">
                @include('home.tournaments.tab.tab-schedule')
            </div>

            {{-- Tab Bảng xếp hạng --}}
            <div class="tab-pane fade" id="ranking-content" role="tabpanel">
                @include('home.tournaments.tab.tab-ranking')
            </div>
        </div>

        {{-- Modal Danh sách người chơi --}}
        <div class="modal fade" id="playerModal" tabindex="-1" aria-labelledby="playerModalLabel" aria-hidden="true">
            {{-- Nếu là Team thì dùng modal-xl, còn Cá nhân thì dùng modal-lg --}}
            <div class="modal-dialog modal-dialog-centered {{ isset($tournament->mode) && $tournament->mode == 'team' ? '' : 'modal-lg' }}"
                style="{{ isset($tournament->mode) && $tournament->mode == 'team' ? 'max-width: 950px;' : '' }}">
                <div class="modal-content bg-dark text-white border-secondary shadow-lg">

                    <div class="modal-header text-white">
                        <h5 class="modal-title" id="playerModalLabel" style="color:#1b7c00">
                            DANH SÁCH
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        @if ($tournament->creator_id == auth()->id() && $tournament->status == 'open')
                            <div class="mb-4">
                                <h5 class="fw-semibold text-warning mb-3">
                                    <i class="bi bi-hourglass-split me-2"></i>Đang chờ duyệt
                                </h5>
                                @if ($tournament->players->where('status', 'pending')->isEmpty())
                                    <p class="fst-italic">Không có ai đang chờ duyệt.</p>
                                @else
                                    <div class="player-list-scroll mb-3">
                                        <ul class="list-group list-group-flush gap-2"> {{-- Thêm gap-2 để các dòng cách nhau ra --}}
                                            @foreach ($tournament->players->where('status', 'pending') as $player)
                                                <li
                                                    class="list-group-item bg-dark text-white border border-secondary rounded d-flex justify-content-between align-items-center p-3 shadow-sm">

                                                    {{-- Tên người chơi --}}
                                                    <span class="fs-5 text-truncate pe-2" style="max-width: 50%;">
                                                        {{ $player->name }}
                                                    </span>

                                                    {{-- Khu vực nút bấm (Căn chỉnh thẳng hàng) --}}
                                                    <div class="d-flex align-items-center gap-2">
                                                        {{-- 1. Nút DUYỆT (Xanh) --}}
                                                        <form action="{{ route('player.approve', $player->id) }}"
                                                            method="POST" class="m-0">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success px-3"
                                                                style="min-width: 90px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i> Duyệt
                                                            </button>
                                                        </form>

                                                        {{-- 2. Nút TỪ CHỐI (Đỏ) --}}
                                                        <form action="{{ route('player.delete', $player->id) }}"
                                                            method="POST" class="m-0"
                                                            onsubmit="return confirm('Bạn có chắc muốn từ chối đội {{ $player->name }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger px-3"
                                                                style="min-width: 100px;">
                                                                <i class="bi bi-x-circle-fill me-1"></i> Từ chối
                                                            </button>
                                                        </form>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div id="add-player-section" class="mb-4">
                                <h5 class="fw-semibold text-info mb-3">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    {{-- Kiểm tra chế độ để hiện chữ phù hợp --}}
                                    {{ isset($tournament->mode) && $tournament->mode == 'team' ? 'Thêm Đội thi đấu' : 'Thêm Người chơi' }}
                                </h5>

                                <form action="{{ route('tournament.addPlayer', $tournament->id) }}" method="POST"
                                    class="ajax-add-player-form {{ $tournament->players->where('status', 'approved')->count() >= $tournament->max_player ? 'd-none' : '' }}">
                                    @csrf
                                    <div class="input-group">
                                        <input type="text" name="name"
                                            class="form-control bg-dark text-white border-secondary"
                                            placeholder="{{ isset($tournament->mode) && $tournament->mode == 'team' ? 'Nhập tên Đội...' : 'Nhập tên người chơi...' }}"
                                            required>
                                        <button class="btn btn-success">Thêm</button>
                                    </div>
                                </form>

                                <p class="text-warning fst-italic {{ $tournament->players->where('status', 'approved')->count() < $tournament->max_player ? 'd-none' : '' }}"
                                    id="full-player-text">
                                    Giải đấu đã đủ số lượng ({{ $tournament->max_player }}).
                                </p>
                            </div>
                        @endif

                        <div>
                            <div class="player-list-scroll">
                                <ul class="list-group list-group-flush" id="approved-player-list">
                                    @forelse ($tournament->players->where('status', 'approved') as $player)
                                        <li class="list-group-item bg-dark text-white p-0">
                                            {{-- Wrapper chính: Dùng d-flex để chia cột --}}
                                            <div class="d-flex w-100 align-items-center">

                                                {{-- PHẦN 1: TÊN (CHIẾM 60%) --}}
                                                <div class="p-3 d-flex justify-content-between align-items-center"
                                                    style="width: 60%;">
                                                    <div class="flex-grow-1">
                                                        <span
                                                            class="me-2 fw-bold text-success player-stt">{{ $loop->iteration }}.</span>
                                                        <span id="name-{{ $player->id }}"
                                                            class="fw-bold fs-5">{{ $player->name }}</span>

                                                        {{-- Form sửa tên --}}
                                                        <form id="form-{{ $player->id }}"
                                                            class="d-none ajax-edit-form d-inline"
                                                            action="{{ route('player.update', $player->id) }}"
                                                            method="POST">
                                                            @csrf @method('PUT')
                                                            <input type="text" name="name"
                                                                value="{{ $player->name }}"
                                                                class="form-control form-control-sm d-inline-block w-auto">
                                                            <button type="submit"
                                                                class="btn btn-sm btn-success">Lưu</button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-secondary cancel-edit"
                                                                data-id="{{ $player->id }}">Hủy</button>
                                                        </form>
                                                    </div>

                                                    {{-- Các nút thao tác --}}
                                                    @if ($tournament->creator_id == auth()->id() && $tournament->status == 'open')
                                                        <div class="ms-2 d-flex align-items-center gap-2">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-warning edit-btn"
                                                                data-id="{{ $player->id }}">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <form class="d-inline ajax-delete-form"
                                                                action="{{ route('player.delete', $player->id) }}"
                                                                method="POST">
                                                                @csrf @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- PHẦN 2: THÀNH VIÊN (CHIẾM 40%) - Chỉ hiện khi mode là team --}}
                                                @if (isset($tournament->mode) && $tournament->mode == 'team')
                                                    <div class="p-2 border-start border-secondary"
                                                        style="width: 40%; border-left: 2px solid #555;">
                                                        <small class="text-white d-block mb-1">Thành viên:</small>

                                                        {{-- Wrapper tạo thanh cuộn (Scroll) --}}
                                                        <div class="member-scroll-wrapper pe-1"
                                                            style="max-height: 100px; overflow-y: auto;">
                                                            <ul
                                                                class="list-unstyled mb-2 member-list-{{ $player->id }}">
                                                                @foreach ($player->members as $member)
                                                                    <li
                                                                        class="d-flex justify-content-between align-items-center text-white small mb-1 bg-secondary bg-opacity-10 px-2 py-1 rounded">
                                                                        <span> {{ $member->member_name }}</span>
                                                                        @if ($tournament->creator_id == auth()->id())
                                                                            <i class="bi bi-x text-danger cursor-pointer delete-member-btn"
                                                                                style="cursor: pointer;"
                                                                                data-url="{{ route('member.delete', $member->id) }}"
                                                                                onclick="deleteMember(this)"></i>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>

                                                        {{-- Form thêm thành viên (Giữ nguyên code cũ, nằm dưới phần scroll) --}}
                                                        @if ($tournament->creator_id == auth()->id())
                                                            <form class="d-flex gap-2 ajax-add-member-form mt-1"
                                                                action="{{ route('member.add', $player->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="text" name="member_name"
                                                                    class="form-control form-control-sm bg-dark text-white border-secondary py-0"
                                                                    style="font-size: 0.85rem;"
                                                                    placeholder="Thêm thành viên..." required>
                                                                <button class="btn btn-sm btn-outline-info py-0">
                                                                    <i class="bi bi-plus"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </li>
                                    @empty
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
                    </div>

                </div>
            </div>
        </div>


        {{-- MODAL ĐĂNG KÝ ĐỘI --}}
        @if ($tournament->mode == 'team')
            <div class="modal fade" id="joinTeamModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content bg-dark text-white border-secondary shadow-lg">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title text-uppercase fw-bold" style="color: #1b7c00">
                                Đăng ký tham gia thi đấu
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <form action="{{ route('tournament.join', $tournament->id) }}" method="POST"
                            class="ajax-join-team-form">
                            @csrf
                            <div class="modal-body">
                                {{-- Tên Đội --}}
                                <div class="mb-4">
                                    <label class="form-label text-white fw-bold">Tên Đội tuyển</label>
                                    <input type="text" name="team_name"
                                        class="form-control bg-dark text-white border-secondary"
                                        placeholder="Ví dụ: T1, GAM Esports..." required>
                                    <div class="form-text text-muted">Bạn sẽ là Đội trưởng của đội này.</div>
                                </div>

                                {{-- Danh sách thành viên --}}
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-white fw-bold mb-0">Danh sách thành viên</label>
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                            id="btn-add-member-input">
                                            <i class="bi bi-plus-lg"></i> Thêm thành viên
                                        </button>
                                    </div>

                                    <div id="member-inputs-container"
                                        class="team-member-scroll border border-secondary rounded p-2">
                                        {{-- Mặc định hiện sẵn 2 dòng --}}
                                        <div class="input-group mb-2">
                                            <span
                                                class="input-group-text bg-secondary border-secondary text-white">1</span>
                                            <input type="text" name="members[]"
                                                class="form-control bg-dark text-white border-secondary"
                                                placeholder="Tên thành viên..." required>
                                        </div>
                                        <div class="input-group mb-2">
                                            <span
                                                class="input-group-text bg-secondary border-secondary text-white">2</span>
                                            <input type="text" name="members[]"
                                                class="form-control bg-dark text-white border-secondary"
                                                placeholder="Tên thành viên..." required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-secondary">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn fw-bold text-white"
                                    style="background-color: #1b7c00">Gửi đăng
                                    ký</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade" id="joinResultModal" tabindex="-1" aria-labelledby="joinResultModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-white border-secondary">
                    <div class="modal-header">
                        <h5 class="modal-title" id="joinResultModalLabel"><i class="bi bi-info-circle me-2"></i>Thông
                            báo
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="joinResultMessage"></div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- SCRIPT QUẢN LÝ NGƯỜI CHƠI & ĐỘI --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const playerList = document.getElementById('approved-player-list');
            const addForm = document.querySelector('.ajax-add-player-form');
            const addSection = document.getElementById('add-player-section');
            const fullText = document.getElementById('full-player-text');

            // Lấy chế độ đấu từ server để JS biết đường vẽ giao diện
            const isTeamMode =
                "{{ isset($tournament->mode) && $tournament->mode == 'team' ? 'true' : 'false' }}" === 'true';
            const maxPlayer = {{ $tournament->max_player }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

            // 1. CẬP NHẬT GIAO DIỆN (Ẩn hiện form thêm)
            function updateAddSection() {
                if (!playerList) return;
                const count = playerList.querySelectorAll('li.list-group-item').length; // Đếm thẻ li chính xác
                if (count >= maxPlayer) {
                    if (addForm) addForm.classList.add('d-none');
                    if (fullText) fullText.classList.remove('d-none');
                } else {
                    if (addForm) addForm.classList.remove('d-none');
                    if (fullText) fullText.classList.add('d-none');
                }
            }

            // 2. CẬP NHẬT SỐ THỨ TỰ
            function updatePlayerIndexes() {
                if (!playerList) return;
                playerList.querySelectorAll("li.list-group-item").forEach((li, index) => {
                    const sttSpan = li.querySelector(".player-stt");
                    if (sttSpan) sttSpan.textContent = (index + 1) + ".";
                });
            }

            // 3. XỬ LÝ SỰ KIỆN CHUNG (EVENT DELEGATION - QUAN TRỌNG)
            // Thay vì gán onclick cho từng nút, ta gán cho cả danh sách
            if (playerList) {
                playerList.addEventListener('click', function(e) {
                    const target = e.target;

                    // A. Nút Sửa (Edit)
                    const editBtn = target.closest('.edit-btn');
                    if (editBtn) {
                        const id = editBtn.dataset.id;
                        document.getElementById(`name-${id}`).classList.add('d-none');
                        document.getElementById(`form-${id}`).classList.remove('d-none');
                        editBtn.classList.add('d-none');
                        return;
                    }

                    // B. Nút Hủy Sửa (Cancel)
                    const cancelBtn = target.closest('.cancel-edit');
                    if (cancelBtn) {
                        const id = cancelBtn.dataset.id;
                        document.getElementById(`name-${id}`).classList.remove('d-none');
                        document.getElementById(`form-${id}`).classList.add('d-none');
                        // Hiện lại nút sửa
                        const originalEditBtn = playerList.querySelector(`.edit-btn[data-id="${id}"]`);
                        if (originalEditBtn) originalEditBtn.classList.remove('d-none');
                        return;
                    }
                });

                // C. Xử lý Submit Form Sửa/Xóa (Event Delegation cho Form)
                // Lưu ý: Sự kiện submit không nổi bọt (bubble) giống click, nhưng focusin/out thì có.
                // Tuy nhiên ta có thể bắt sự kiện submit ở document và kiểm tra target.
                document.addEventListener('submit', async function(e) {
                    const form = e.target;

                    // Nếu là Form Sửa Tên
                    if (form.classList.contains('ajax-edit-form')) {
                        e.preventDefault();
                        const id = form.id.replace('form-', '');
                        const input = form.querySelector('input[name="name"]');
                        const formData = new FormData(form);
                        formData.append('_method', 'PUT');

                        try {
                            const res = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: formData
                            });

                            if (res.ok) {
                                // Cập nhật giao diện
                                const nameSpan = document.getElementById(`name-${id}`);
                                nameSpan.textContent = input.value;
                                nameSpan.classList.remove('d-none');
                                form.classList.add('d-none');

                                const editBtn = playerList.querySelector(`.edit-btn[data-id="${id}"]`);
                                if (editBtn) editBtn.classList.remove('d-none');
                            } else {
                                alert('Cập nhật thất bại!');
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Lỗi kết nối');
                        }
                    }

                    // Nếu là Form Xóa Người Chơi/Đội
                    if (form.classList.contains('ajax-delete-form')) {
                        e.preventDefault();
                        if (!confirm('Bạn chắc chắn muốn xóa?')) return;

                        const li = form.closest('li.list-group-item');
                        const formData = new FormData(form);
                        formData.append('_method', 'DELETE');

                        try {
                            const res = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: formData
                            });

                            if (res.ok) {
                                li.remove();
                                updateAddSection();
                                updatePlayerIndexes();
                            } else {
                                alert('Xóa thất bại!');
                            }
                        } catch (err) {
                            console.error(err);
                        }
                    }
                });
            }

            // 4. XỬ LÝ THÊM MỚI (AJAX ADD)
            if (addForm) {
                addForm.onsubmit = async (e) => {
                    e.preventDefault();
                    const input = addForm.querySelector('input[name="name"]');
                    const btn = addForm.querySelector('button');

                    btn.disabled = true; // Chống spam click

                    try {
                        const res = await fetch(addForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: new FormData(addForm)
                        });

                        if (res.ok) {
                            const data = await res.json();

                            // Tạo HTML cho dòng mới (Tương thích cả Cá nhân và Đội)
                            const li = document.createElement('li');
                            li.className =
                                'list-group-item bg-dark text-white border-secondary mb-2 rounded p-2';

                            // Phần HTML dành riêng cho Team (nếu có)
                            const teamMembersHtml = isTeamMode ? `
                                <div class="mt-2 ps-4 border-start border-secondary" style="border-left: 2px solid #555;">
                                    <small class="text-muted d-block mb-1">Thành viên:</small>
                                    <ul class="list-unstyled mb-2 member-list-${data.id}">
                                        </ul>
                                    <form class="d-flex gap-2 ajax-add-member-form" action="/players/${data.id}/members" method="POST">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="text" name="member_name" class="form-control form-control-sm bg-dark text-white border-secondary py-0" style="font-size: 0.85rem;" placeholder="Thêm thành viên..." required>
                                        <button class="btn btn-sm btn-outline-info py-0"><i class="bi bi-plus"></i></button>
                                    </form>
                                </div>
                            ` : '';

                            // Route update/delete (Giả định URL chuẩn, nếu khác bạn cần sửa lại)
                            // Lưu ý: data.id trả về từ controller
                            const updateUrl = `/player/${data.id}`;
                            const deleteUrl = `/player/${data.id}`;

                            li.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <span class="me-2 fw-bold text-success player-stt"></span>
                                        <span id="name-${data.id}" class="fw-bold fs-5">${data.name}</span>

                                        <form id="form-${data.id}" class="d-none ajax-edit-form d-inline" action="${updateUrl}" method="POST">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="text" name="name" value="${data.name}" class="form-control form-control-sm d-inline-block w-auto">
                                            <button type="submit" class="btn btn-sm btn-success">Lưu</button>
                                            <button type="button" class="btn btn-sm btn-secondary cancel-edit" data-id="${data.id}">Hủy</button>
                                        </form>
                                    </div>

                                    <div class="ms-2 d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-warning edit-btn" data-id="${data.id}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form class="d-inline ajax-delete-form" action="${deleteUrl}" method="POST">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                                ${teamMembersHtml}
                            `;

                            playerList.appendChild(li);
                            input.value = '';
                            updateAddSection();
                            updatePlayerIndexes();

                            // Quan trọng: Gán lại sự kiện cho form thêm thành viên mới vừa sinh ra
                            if (isTeamMode) {
                                attachMemberFormEvent(li.querySelector('.ajax-add-member-form'));
                            }

                        } else {
                            alert('Thêm thất bại!');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Lỗi kết nối');
                    } finally {
                        btn.disabled = false;
                    }
                };
            }

            // Hàm gán sự kiện cho form thêm thành viên (Tách riêng để tái sử dụng)
            function attachMemberFormEvent(form) {
                if (!form) return;
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    // ... (Logic thêm thành viên giống script cũ của bạn, copy vào đây hoặc gọi hàm chung) ...
                    // Để code gọn, phần này sẽ được xử lý bởi block script "XỬ LÝ THÊM THÀNH VIÊN" ở dưới cùng file
                    // Tuy nhiên, vì form được sinh ra động, ta cần kích hoạt thủ công sự kiện submit của nó
                    // Cách tốt nhất: dùng Event Delegation cho cả form thêm thành viên
                });
            }

            // Chạy lần đầu
            updateAddSection();
        });
    </script>

    {{-- Nút đăng ký tham gia --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const joinForm = document.querySelector('.ajax-join-form');

            if (joinForm) {
                joinForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const btn = joinForm.querySelector('button');
                    const originalText = btn.innerHTML;

                    // 1. Khóa nút ngay lập tức để tránh bấm nhiều lần & báo đang xử lý
                    btn.disabled = true;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

                    try {
                        const res = await fetch(joinForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': joinForm.querySelector('input[name="_token"]')
                                    .value,
                                'Accept': 'application/json'
                            },
                            body: new FormData(joinForm)
                        });

                        const modalMessage = document.getElementById('joinResultMessage');
                        const modalElement = document.getElementById('joinResultModal');

                        // Lấy dữ liệu phản hồi (dù thành công hay thất bại)
                        let data;
                        try {
                            data = await res.json();
                        } catch (err) {
                            data = {
                                message: "Lỗi phản hồi từ server!"
                            };
                        }

                        // 2. HIỆN MODAL ĐẸP
                        if (modalElement && modalMessage && typeof bootstrap !== 'undefined') {
                            const modal = new bootstrap.Modal(modalElement);
                            modalMessage.textContent = data.message || "Đã gửi yêu cầu.";
                            modal.show();
                        }
                        // 3. HIỆN ALERT (Dự phòng nếu Modal lỗi)
                        else {
                            alert(data.message || "Đã gửi yêu cầu.");
                        }

                        // 4. Xử lý nút bấm sau khi xong
                        if (res.ok && data.status === 'success') {
                            btn.innerHTML = '<i class="bi bi-check-lg"></i> Đã gửi';
                            btn.classList.remove('btn-primary');
                            btn.classList.add('btn-secondary');
                            // Giữ nguyên disabled
                        } else {
                            // Nếu lỗi hoặc chỉ là warning (đã đăng ký rồi) thì mở lại nút hoặc giữ nguyên tùy ý
                            // Ở đây tôi mở lại nút để họ biết
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }

                    } catch (error) {
                        console.error("Lỗi JS:", error);
                        alert('Lỗi kết nối! Vui lòng kiểm tra mạng.');

                        // Mở lại nút khi lỗi mạng
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                });
            }
        });
    </script>



    {{-- Chỉnh sửa modal thêm đội --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const playerList = document.getElementById('approved-player-list');
            const addForm = document.querySelector('.ajax-add-player-form');
            const addSection = document.getElementById('add-player-section');
            const fullText = document.getElementById('full-player-text');

            // Lấy chế độ đấu
            const isTeamMode =
                "{{ isset($tournament->mode) && $tournament->mode == 'team' ? 'true' : 'false' }}" === 'true';
            const maxPlayer = {{ $tournament->max_player }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

            // 1. CẬP NHẬT GIAO DIỆN (Ẩn hiện form thêm)
            function updateAddSection() {
                if (!playerList) return;
                const count = playerList.querySelectorAll('li.list-group-item').length;
                if (count >= maxPlayer) {
                    if (addForm) addForm.classList.add('d-none');
                    if (fullText) fullText.classList.remove('d-none');
                } else {
                    if (addForm) addForm.classList.remove('d-none');
                    if (fullText) fullText.classList.add('d-none');
                }
            }

            // 2. CẬP NHẬT SỐ THỨ TỰ
            function updatePlayerIndexes() {
                if (!playerList) return;
                playerList.querySelectorAll("li.list-group-item").forEach((li, index) => {
                    const sttSpan = li.querySelector(".player-stt");
                    if (sttSpan) sttSpan.textContent = (index + 1) + ".";
                });
            }

            // 3. XỬ LÝ SỰ KIỆN CLICK (Sửa/Hủy/Xóa)
            if (playerList) {
                playerList.addEventListener('click', function(e) {
                    const target = e.target;

                    // Nút Sửa
                    const editBtn = target.closest('.edit-btn');
                    if (editBtn) {
                        const id = editBtn.dataset.id;
                        document.getElementById(`name-${id}`).classList.add('d-none');
                        document.getElementById(`form-${id}`).classList.remove('d-none');
                        editBtn.classList.add('d-none');
                    }

                    // Nút Hủy
                    const cancelBtn = target.closest('.cancel-edit');
                    if (cancelBtn) {
                        const id = cancelBtn.dataset.id;
                        document.getElementById(`name-${id}`).classList.remove('d-none');
                        document.getElementById(`form-${id}`).classList.add('d-none');
                        const originalBtn = playerList.querySelector(`.edit-btn[data-id="${id}"]`);
                        if (originalBtn) originalBtn.classList.remove('d-none');
                    }
                });
            }

            // 4. XỬ LÝ SUBMIT FORM (Sửa tên / Xóa người / Thêm thành viên)
            document.addEventListener('submit', async function(e) {
                const form = e.target;

                // A. Form thêm thành viên (Quan trọng: Xử lý giao diện giống hệt Server)
                if (form.classList.contains('ajax-add-member-form')) {
                    e.preventDefault();
                    const input = form.querySelector('input[name="member_name"]');
                    const btn = form.querySelector('button');
                    const originalHtml = btn.innerHTML;

                    btn.disabled = true;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm" style="width: 0.7rem; height: 0.7rem;"></span>';

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: new FormData(form)
                        });
                        const data = await res.json();

                        if (data.success) {
                            const ul = form.previousElementSibling; // Tìm thẻ UL ngay trên form

                            // Tạo dòng thành viên mới (Copy y hệt Blade)
                            const li = document.createElement('li');
                            li.className =
                                'd-flex justify-content-between align-items-center text-white small mb-1 bg-secondary bg-opacity-10 px-2 py-1 rounded';

                            // Nút xóa thành viên
                            // Lưu ý: data.id là ID thành viên server trả về
                            li.innerHTML = `
                            <span>${input.value}</span>
                            <i class="bi bi-x text-danger cursor-pointer"
                               style="cursor: pointer;"
                               onclick="deleteMemberById(this, ${data.id})"></i>
                        `;

                            ul.appendChild(li);
                            input.value = '';
                        } else {
                            alert('Lỗi: ' + data.message);
                        }
                    } catch (err) {
                        console.error(err);
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                }

                // B. Form Sửa Tên Đội/Người
                if (form.classList.contains('ajax-edit-form')) {
                    e.preventDefault();
                    const id = form.id.replace('form-', '');
                    const input = form.querySelector('input[name="name"]');
                    const formData = new FormData(form);
                    formData.append('_method', 'PUT');

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        });
                        if (res.ok) {
                            document.getElementById(`name-${id}`).textContent = input.value;
                            document.getElementById(`name-${id}`).classList.remove('d-none');
                            form.classList.add('d-none');
                            const editBtn = playerList.querySelector(`.edit-btn[data-id="${id}"]`);
                            if (editBtn) editBtn.classList.remove('d-none');
                        }
                    } catch (err) {
                        alert('Lỗi kết nối');
                    }
                }

                // C. Form Xóa Đội/Người
                if (form.classList.contains('ajax-delete-form')) {
                    e.preventDefault();
                    if (!confirm('Xóa đội/người chơi này?')) return;
                    const li = form.closest('li.list-group-item');
                    const formData = new FormData(form);
                    formData.append('_method', 'DELETE');
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        });
                        if (res.ok) {
                            li.remove();
                            updateAddSection();
                            updatePlayerIndexes();
                        }
                    } catch (err) {
                        alert('Lỗi xóa');
                    }
                }
            });

            // 5. XỬ LÝ THÊM ĐỘI / NGƯỜI CHƠI (AJAX ADD)
            if (addForm) {
                addForm.onsubmit = async (e) => {
                    e.preventDefault();
                    const input = addForm.querySelector('input[name="name"]');
                    const btn = addForm.querySelector('button');
                    btn.disabled = true;

                    try {
                        const res = await fetch(addForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: new FormData(addForm)
                        });

                        if (res.ok) {
                            const data = await res.json();

                            // Tạo thẻ li chính
                            const li = document.createElement('li');
                            li.className =
                                'list-group-item bg-dark text-white border-secondary mb-2 rounded p-2';

                            // HTML cho phần thành viên (Đồng bộ class form-control-sm và py-0 để không bị to)
                            const teamMembersHtml = isTeamMode ? `
                            <div class="mt-2 ps-4 border-start border-secondary" style="border-left: 2px solid #555;">
                                <small class="text-muted d-block mb-1">Thành viên:</small>
                                <ul class="list-unstyled mb-2 member-list-${data.id}"></ul>
                                <form class="d-flex gap-2 ajax-add-member-form" action="/players/${data.id}/members" method="POST">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="text" name="member_name"
                                           class="form-control form-control-sm bg-dark text-white border-secondary py-0"
                                           style="font-size: 0.85rem;" placeholder="Thêm thành viên..." required>
                                    <button class="btn btn-sm btn-outline-info py-0"><i class="bi bi-plus"></i></button>
                                </form>
                            </div>
                        ` : '';

                            // HTML nội dung thẻ li
                            li.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <span class="me-2 fw-bold text-success player-stt"></span>
                                    <span id="name-${data.id}" class="fw-bold fs-5">${data.name}</span>

                                    <form id="form-${data.id}" class="d-none ajax-edit-form d-inline" action="/player/${data.id}" method="POST">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="text" name="name" value="${data.name}" class="form-control form-control-sm d-inline-block w-auto">
                                        <button type="submit" class="btn btn-sm btn-success">Lưu</button>
                                        <button type="button" class="btn btn-sm btn-secondary cancel-edit" data-id="${data.id}">Hủy</button>
                                    </form>
                                </div>
                                <div class="ms-2 d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-btn" data-id="${data.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form class="d-inline ajax-delete-form" action="/player/${data.id}" method="POST">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            ${teamMembersHtml}
                        `;

                            playerList.appendChild(li);
                            input.value = '';
                            updateAddSection();
                            updatePlayerIndexes();
                        }
                    } catch (err) {
                        console.error(err);
                    } finally {
                        btn.disabled = false;
                    }
                };
            }

            // Hàm xóa thành viên (Có sẵn)
            window.deleteMember = async function(icon) {
                if (!confirm('Xóa thành viên này?')) return;
                const url = icon.dataset.url;
                try {
                    const res = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (res.ok) icon.parentElement.remove();
                } catch (err) {
                    alert('Lỗi khi xóa');
                }
            }

            // Hàm xóa thành viên (Vừa thêm mới)
            window.deleteMemberById = async function(icon, id) {
                if (!confirm('Xóa thành viên này?')) return;
                try {
                    // Dùng id server trả về để gọi route xóa
                    await fetch(`/members/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    icon.parentElement.remove();
                } catch (err) {
                    console.error(err);
                }
            }

            // Chạy lần đầu
            updateAddSection();
        });
    </script>

    {{-- MODAL ĐĂNG KÝ ĐỘI --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hàm cập nhật lại số thứ tự (1, 2, 3...)
            function updateIndices() {
                const container = document.getElementById('member-inputs-container');
                if (!container) return;

                // Lấy tất cả các dòng input-group
                const rows = container.querySelectorAll('.input-group');

                rows.forEach((row, index) => {
                    // Tìm thẻ span chứa số thứ tự
                    const span = row.querySelector('.input-group-text');
                    if (span) {
                        span.textContent = index + 1; // Gán số thứ tự mới (index bắt đầu từ 0 nên phải +1)
                    }
                });
            }

            // 1. XỬ LÝ THÊM DÒNG
            const addMemberBtn = document.getElementById('btn-add-member-input');
            const memberContainer = document.getElementById('member-inputs-container');

            if (addMemberBtn && memberContainer) {
                addMemberBtn.addEventListener('click', function() {
                    const div = document.createElement('div');
                    div.className = 'input-group mb-2 animate__animated animate__fadeIn';

                    // Lưu ý: Nút xóa bây giờ gọi hàm removeMemberRow(this) thay vì remove() trực tiếp
                    div.innerHTML = `
                        <span class="input-group-text bg-secondary border-secondary text-white"></span>
                        <input type="text" name="members[]" class="form-control bg-dark text-white border-secondary" placeholder="Tên thành viên..." required>
                        <button type="button" class="btn btn-outline-danger" onclick="removeMemberRow(this)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    `;
                    memberContainer.appendChild(div);

                    // Gọi hàm đánh số lại ngay sau khi thêm
                    updateIndices();
                });
            }

            // Hàm xóa dòng (được gọi từ onclick của nút X)
            window.removeMemberRow = function(btn) {
                // Xóa dòng chứa nút đó
                btn.parentElement.remove();
                // Quan trọng: Gọi hàm đánh số lại sau khi xóa
                updateIndices();
            }

            // 2. XỬ LÝ AJAX GỬI FORM ĐỘI (BẢN FINAL FIX LỖI)
            const joinTeamForm = document.querySelector('.ajax-join-team-form');
            if (joinTeamForm) {
                joinTeamForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const btn = this.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;

                    // 1. Khóa nút ngay lập tức
                    btn.disabled = true;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm"></span> Đang gửi...';

                    try {
                        const res = await fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json' // Quan trọng: Báo server trả về JSON
                            },
                            body: new FormData(this)
                        });

                        // Đọc text trước để debug
                        const text = await res.text();
                        console.log("Server trả về:", text); // <--- BẠN XEM CÁI NÀY TRONG F12 NẾU LỖI

                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (err) {
                            throw new Error(
                                "Dữ liệu trả về không đúng định dạng JSON. Xem Console F12.");
                        }

                        // 2. Tắt Modal nhập liệu
                        const teamModalEl = document.getElementById('joinTeamModal');
                        const teamModal = bootstrap.Modal.getInstance(teamModalEl);
                        if (teamModal) teamModal.hide();

                        // 3. Hiện thông báo
                        const msgModalEl = document.getElementById('joinResultModal');
                        const msgContent = document.getElementById('joinResultMessage');

                        if (msgModalEl && msgContent) {
                            let icon = '';
                            let colorClass = '';

                            if (data.status === 'success') {
                                icon =
                                    '<i class="bi bi-check-circle-fill text-success me-2" style="font-size: 2rem;"></i>';
                                colorClass = 'text-white';
                            } else if (data.status === 'warning') {
                                icon =
                                    '<i class="bi bi-exclamation-triangle-fill text-warning me-2" style="font-size: 2rem;"></i>';
                                colorClass = 'text-warning';
                            } else {
                                icon =
                                    '<i class="bi bi-x-circle-fill text-danger me-2" style="font-size: 2rem;"></i>';
                                colorClass = 'text-danger';
                            }

                            msgContent.innerHTML = `
                                <div class="text-center py-3">
                                    <div class="mb-3">${icon}</div>
                                    <h5 class="${colorClass} fw-bold">${data.message || 'Có lỗi xảy ra'}</h5>
                                </div>
                            `;

                            const msgModal = new bootstrap.Modal(msgModalEl);
                            msgModal.show();
                        } else {
                            alert(data.message);
                        }

                        // 4. Xử lý nút bấm sau khi xong
                        if (res.ok && data.status === 'success') {
                            const mainBtn = document.querySelector('[data-bs-target="#joinTeamModal"]');
                            if (mainBtn) {
                                mainBtn.disabled = true;
                                mainBtn.innerHTML = '<i class="bi bi-check-lg"></i> Đã gửi yêu cầu';
                                mainBtn.classList.remove('btn-primary');
                                mainBtn.classList.add('btn-secondary');
                            }
                        } else {
                            // Mở lại nút để gửi lại nếu lỗi
                            btn.disabled = false;
                            btn.innerHTML = originalText;

                            // Tự động mở lại form để sửa nếu không thành công
                            if (data.status !== 'success' && teamModal) {
                                setTimeout(() => teamModal.show(), 500);
                            }
                        }

                    } catch (err) {
                        console.error(err);
                        alert('Lỗi: ' + err.message);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                });
            }

            // Chạy update lần đầu để đảm bảo số 1, 2 hiện đúng
            updateIndices();
        });
    </script>
@endsection
