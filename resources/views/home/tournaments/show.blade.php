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
                            class="img-fluid rounded shadow" style="height: 300px; width: 50%; object-fit: cover;">
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
                                        <ul class="list-group list-group-flush gap-2" id="pending-player-list">
                                            {{-- Thêm gap-2 để các dòng cách nhau ra --}}
                                            @foreach ($tournament->players->where('status', 'pending') as $player)
                                                <li class="list-group-item bg-dark text-white border border-secondary rounded d-flex justify-content-between align-items-center p-3 shadow-sm"
                                                    id="pending-item-{{ $player->id }}">

                                                    {{-- Tên người chơi --}}
                                                    <span class="fs-5 text-truncate pe-2" style="max-width: 50%;">
                                                        {{ $player->name }}
                                                    </span>

                                                    {{-- Khu vực nút bấm --}}
                                                    <div class="d-flex align-items-center gap-2">
                                                        {{-- 1. Nút DUYỆT (Thêm class pending-action-form và data-type="approve") --}}
                                                        <form action="{{ route('player.approve', $player->id) }}"
                                                            method="POST" class="m-0 pending-action-form"
                                                            data-type="approve">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success px-3"
                                                                style="min-width: 90px;">
                                                                Duyệt
                                                            </button>
                                                        </form>

                                                        {{-- 2. Nút TỪ CHỐI (Thêm class pending-action-form và data-type="reject") --}}
                                                        <form action="{{ route('player.delete', $player->id) }}"
                                                            method="POST" class="m-0 pending-action-form"
                                                            data-type="reject">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger px-3"
                                                                style="min-width: 100px;">
                                                                Từ chối
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
                                    <div class="input-group has-validation"> {{-- Thêm has-validation để giữ bo góc đẹp --}}
                                        <input type="text" name="name" id="add-player-input" {{-- Thêm ID để dễ gọi --}}
                                            class="form-control bg-dark text-white border-secondary"
                                            placeholder="{{ isset($tournament->mode) && $tournament->mode == 'team' ? 'Nhập tên Đội...' : 'Nhập tên người chơi...' }}"
                                            required>
                                        <button class="btn btn-success">Thêm</button>

                                        {{-- Thêm thẻ này để hiện lỗi --}}
                                        <div id="add-player-error" class="invalid-feedback text-start"></div>
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
                                                    style="width: {{ isset($tournament->mode) && $tournament->mode == 'team' ? '60%' : '100%' }};">
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
                                                                        @if ($tournament->creator_id == auth()->id() && $tournament->status == 'open')
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
                                                        @if ($tournament->creator_id == auth()->id() && $tournament->status == 'open')
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
                    <div class="modal-body text-center mt-4 fw-bold fs-5" id="joinResultMessage"></div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
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


            const deleteModalEl = document.getElementById('deleteConfirmModal');
            let deleteModal = null;
            let confirmBtn = null;
            let pendingDeleteAction = null; // Biến lưu hành động xóa chờ thực hiện

            if (deleteModalEl) {
                deleteModal = new bootstrap.Modal(deleteModalEl);
                confirmBtn = document.getElementById('confirmDeleteBtn');

                // Khi bấm nút "Xóa ngay" trong Modal -> Chạy hành động đã lưu
                confirmBtn.addEventListener('click', () => {
                    if (pendingDeleteAction) {
                        pendingDeleteAction(); // Chạy hàm xóa thật
                        deleteModal.hide(); // Tắt modal
                        pendingDeleteAction = null; // Reset
                    }
                });
            }
            // Lấy chế độ đấu từ server
            const isTeamMode =
                "{{ isset($tournament->mode) && $tournament->mode == 'team' ? 'true' : 'false' }}" === 'true';
            const maxPlayer = {{ $tournament->max_player }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

            // ==========================================
            // 1. CÁC HÀM HỖ TRỢ (UI UPDATE)
            // ==========================================

            // Cập nhật ẩn hiện form thêm đội khi đủ số lượng
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

            // Cập nhật số thứ tự (1, 2, 3...)
            function updatePlayerIndexes() {
                if (!playerList) return;
                playerList.querySelectorAll("li.list-group-item").forEach((li, index) => {
                    const sttSpan = li.querySelector(".player-stt");
                    if (sttSpan) sttSpan.textContent = (index + 1) + ".";
                });
            }

            // ==========================================
            // 2. HÀM XÓA THÀNH VIÊN (GLOBAL)
            // ==========================================

            // Hàm này cần khai báo window để html onclick gọi được
            window.deleteMember = async function(icon) {
                // Dùng confirm mặc định cho nhanh.
                // Nếu muốn xóa NGAY LẬP TỨC (không hỏi gì cả) thì xóa dòng if bên dưới đi.
                // if (!confirm('Xóa thành viên này?')) return;

                const url = icon.dataset.url;
                try {
                    const res = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (res.ok) {
                        icon.closest('li').remove();
                    } else {
                        alert('Lỗi: Không thể xóa thành viên.');
                    }
                } catch (err) {
                    console.error(err);
                }
            }

            // Hàm xóa thành viên (vừa thêm mới bằng Ajax)
            window.deleteMemberById = async function(icon, id) {
                // Dùng confirm mặc định cho nhanh
                // if (!confirm('Xóa thành viên này?')) return;

                try {
                    await fetch(`/members/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    icon.closest('li').remove();
                } catch (err) {
                    console.error(err);
                }
            }

            // ==========================================
            // 3. XỬ LÝ SỰ KIỆN CLICK (DELEGATION)
            // ==========================================
            if (playerList) {
                playerList.addEventListener('click', function(e) {
                    const target = e.target;

                    // Nút Sửa Đội/Người chơi
                    const editBtn = target.closest('.edit-btn');
                    if (editBtn) {
                        const id = editBtn.dataset.id;
                        document.getElementById(`name-${id}`).classList.add('d-none');
                        document.getElementById(`form-${id}`).classList.remove('d-none');
                        editBtn.classList.add('d-none');
                        return;
                    }

                    // Nút Hủy Sửa
                    const cancelBtn = target.closest('.cancel-edit');
                    if (cancelBtn) {
                        const id = cancelBtn.dataset.id;
                        document.getElementById(`name-${id}`).classList.remove('d-none');
                        document.getElementById(`form-${id}`).classList.add('d-none');
                        const originalEditBtn = playerList.querySelector(`.edit-btn[data-id="${id}"]`);
                        if (originalEditBtn) originalEditBtn.classList.remove('d-none');
                        return;
                    }
                });
            }

            // ==========================================
            // 4. XỬ LÝ SUBMIT CÁC FORM (DELEGATION)
            // ==========================================
            document.addEventListener('submit', async function(e) {
                const form = e.target;

                // A. XỬ LÝ FORM THÊM THÀNH VIÊN VÀO ĐỘI
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
                            // Tìm thẻ UL danh sách thành viên nằm ngay trên form này
                            // Cấu trúc: div > (small + div.scroll > ul) + form
                            // Ta cần tìm thẻ UL trong div wrapper trước đó
                            const wrapper = form.previousElementSibling;
                            const ul = wrapper.querySelector('ul');

                            // Tạo HTML dòng thành viên mới
                            const li = document.createElement('li');
                            li.className =
                                'd-flex justify-content-between align-items-center text-white small mb-1 bg-secondary bg-opacity-10 px-2 py-1 rounded';

                            // Icon xóa gọi hàm deleteMemberById vì đây là item mới
                            li.innerHTML = `
                            <span>${input.value}</span>
                            <i class="bi bi-x text-danger cursor-pointer"
                               style="cursor: pointer;"
                               onclick="deleteMemberById(this, ${data.id})"></i>
                        `;

                            ul.appendChild(li);
                            input.value = '';
                        } else {
                            alert('Lỗi: ' + (data.message || 'Không thể thêm thành viên'));
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Lỗi kết nối');
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                    return; // Kết thúc xử lý form này
                }

                // B. XỬ LÝ FORM SỬA TÊN ĐỘI/NGƯỜI
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
                    }
                    return;
                }

                // C. XỬ LÝ FORM XÓA ĐỘI/NGƯỜI CHƠI
                if (form.classList.contains('ajax-delete-form')) {
                    e.preventDefault();

                    if (!deleteModal) {
                        if (!confirm('Xóa mục này?')) return;
                        // Fallback logic...
                        return;
                    }

                    pendingDeleteAction = async () => {
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
                    };
                    deleteModal.show(); // Hiện modal
                    return;
                }
            });

            // ==========================================
            // 5. XỬ LÝ THÊM ĐỘI / NGƯỜI CHƠI (AJAX ADD MAIN)
            // ==========================================
            if (addForm) {
                const inputElement = addForm.querySelector('input[name="name"]');
                const errorElement = document.getElementById('add-player-error');

                // Khi người dùng gõ lại thì xóa lỗi đi cho đẹp
                inputElement.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    if (errorElement) errorElement.textContent = '';
                });

                addForm.onsubmit = async (e) => {
                    e.preventDefault();
                    const btn = addForm.querySelector('button');

                    // Reset lỗi cũ
                    inputElement.classList.remove('is-invalid');
                    if (errorElement) errorElement.textContent = '';

                    btn.disabled = true;

                    try {
                        const res = await fetch(addForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json' // Quan trọng: Để Laravel trả về JSON lỗi thay vì redirect
                            },
                            body: new FormData(addForm)
                        });

                        const data = await res.json();

                        if (res.ok) {
                            // --- THÀNH CÔNG (Code cũ giữ nguyên) ---
                            const li = document.createElement('li');
                            li.className = 'list-group-item bg-dark text-white p-0';
                            const nameColWidth = isTeamMode ? 'width: 60%;' : 'width: 100%;';

                            // HTML Cột thành viên (Nếu là Team)
                            const teamColHtml = isTeamMode ? `
                            <div class="p-2 border-start border-secondary" style="width: 40%; border-left: 2px solid #555;">
                                <small class="text-white d-block mb-1">Thành viên:</small>
                                <div class="member-scroll-wrapper pe-1" style="max-height: 100px; overflow-y: auto;">
                                    <ul class="list-unstyled mb-2 member-list-${data.id}"></ul>
                                </div>
                                <form class="d-flex gap-2 ajax-add-member-form mt-1" action="/players/${data.id}/members" method="POST">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="text" name="member_name"
                                        class="form-control form-control-sm bg-dark text-white border-secondary py-0"
                                        style="font-size: 0.85rem;" placeholder="Thêm thành viên..." required>
                                    <button class="btn btn-sm btn-outline-info py-0"><i class="bi bi-plus"></i></button>
                                </form>
                            </div>
                        ` : '';

                            // HTML Chính
                            li.innerHTML = `
                            <div class="d-flex w-100 align-items-center">
                                <div class="p-3 d-flex justify-content-between align-items-center" style="${nameColWidth}">
                                    <div class="flex-grow-1">
                                        <span class="me-2 fw-bold text-success player-stt"></span>
                                        <span id="name-${data.id}" class="fw-bold fs-5">${data.name}</span>

                                        <form id="form-${data.id}" class="d-none ajax-edit-form d-inline ms-2" action="/player/${data.id}" method="POST">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="text" name="name" value="${data.name}" class="form-control form-control-sm d-inline-block w-auto">
                                            <button type="submit" class="btn btn-sm btn-success">Lưu</button>
                                            <button type="button" class="btn btn-sm btn-secondary cancel-edit" data-id="${data.id}">Hủy</button>
                                        </form>
                                    </div>
                                    <div class="ms-2 d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-warning edit-btn" data-id="${data.id}"><i class="bi bi-pencil"></i></button>
                                        <form class="d-inline ajax-delete-form" action="/player/${data.id}" method="POST">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                                ${teamColHtml}
                            </div>
                        `;

                            playerList.appendChild(li);
                            inputElement.value = ''; // Xóa input
                            updateAddSection();
                            updatePlayerIndexes();
                        } else {
                            // --- XỬ LÝ LỖI (Status 422 là lỗi Validate) ---
                            if (res.status === 422) {
                                inputElement.classList.add('is-invalid'); // Hiện viền đỏ
                                // Lấy message lỗi từ Laravel trả về
                                if (data.errors && data.errors.name) {
                                    errorElement.textContent = data.errors.name[0];
                                } else {
                                    errorElement.textContent = data.message;
                                }
                            } else {
                                alert(data.error || 'Thêm thất bại!');
                            }
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Lỗi kết nối');
                    } finally {
                        btn.disabled = false;
                    }
                };
            }

            // ==========================================
            // 6. XỬ LÝ DUYỆT / TỪ CHỐI (KHÔNG RELOAD, KHÔNG POPUP)
            // ==========================================
            document.addEventListener('submit', async function(e) {
                // Chỉ bắt sự kiện của form trong danh sách chờ duyệt
                if (e.target.classList.contains('pending-action-form')) {
                    e.preventDefault();

                    const form = e.target;
                    const btn = form.querySelector('button');
                    const originalContent = btn.innerHTML;
                    const type = form.dataset.type; // 'approve' hoặc 'reject'
                    const listItem = form.closest('li'); // Dòng chứa người chơi

                    // Hiệu ứng loading nhỏ trên nút
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });

                        const data = await res.json();

                        if (data.success) {
                            // 1. Xóa khỏi danh sách chờ duyệt
                            listItem.remove();

                            // 2. Nếu là DUYỆT -> Thêm xuống danh sách chính thức
                            if (type === 'approve' && data.player) {
                                addApprovedPlayerToUI(data.player, data.members);
                                updatePlayerIndexes(); // Cập nhật lại số thứ tự
                                updateAddSection(); // Cập nhật lại trạng thái nút thêm
                            }

                            // Kiểm tra nếu danh sách chờ trống thì hiện text "Không có ai..."
                            const pendingList = document.getElementById('pending-player-list');
                            if (pendingList && pendingList.children.length === 0) {
                                pendingList.innerHTML =
                                    '<span class="fst-italic">Không có ai đang chờ duyệt.</span>';
                            }

                        } else {
                            // Chỉ hiện alert nếu lỗi server trả về (ít khi xảy ra)
                            console.error('Action failed');
                        }
                    } catch (err) {
                        console.error(err);
                    } finally {
                        // Nếu item chưa bị xóa (trường hợp lỗi), trả lại nút bấm
                        if (listItem && listItem.parentNode) {
                            btn.disabled = false;
                            btn.innerHTML = originalContent;
                        }
                    }
                }
            });

            // Hàm phụ: Vẽ HTML người chơi đã duyệt để thêm xuống dưới (Copy style từ code cũ)
            function addApprovedPlayerToUI(player, members) {
                const list = document.getElementById('approved-player-list');
                const li = document.createElement('li');
                li.className = 'list-group-item bg-dark text-white p-0';

                const nameColWidth = isTeamMode ? 'width: 60%;' : 'width: 100%;';

                // Tạo HTML danh sách thành viên (nếu là team)
                let membersHtml = '';
                if (isTeamMode) {
                    let membersListHtml = '';
                    if (members && members.length > 0) {
                        members.forEach(m => {
                            membersListHtml += `
                        <li class="d-flex justify-content-between align-items-center text-white small mb-1 bg-secondary bg-opacity-10 px-2 py-1 rounded">
                            <span>${m.member_name}</span>
                            <i class="bi bi-x text-danger cursor-pointer delete-member-btn"
                               style="cursor: pointer;"
                               data-url="/members/${m.id}"
                               onclick="deleteMember(this)"></i>
                        </li>
                    `;
                        });
                    }

                    membersHtml = `
                <div class="p-2 border-start border-secondary" style="width: 40%; border-left: 2px solid #555;">
                    <small class="text-white d-block mb-1">Thành viên:</small>
                    <div class="member-scroll-wrapper pe-1" style="max-height: 100px; overflow-y: auto;">
                        <ul class="list-unstyled mb-2 member-list-${player.id}">
                            ${membersListHtml}
                        </ul>
                    </div>
                    <form class="d-flex gap-2 ajax-add-member-form mt-1" action="/players/${player.id}/members" method="POST">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="text" name="member_name"
                            class="form-control form-control-sm bg-dark text-white border-secondary py-0"
                            style="font-size: 0.85rem;" placeholder="Thêm thành viên..." required>
                        <button class="btn btn-sm btn-outline-info py-0"><i class="bi bi-plus"></i></button>
                    </form>
                </div>
            `;
                }

                // HTML chính
                li.innerHTML = `
            <div class="d-flex w-100 align-items-center">
                <div class="p-3 d-flex justify-content-between align-items-center" style="${nameColWidth}">
                    <div class="flex-grow-1">
                        <span class="me-2 fw-bold text-success player-stt"></span>
                        <span id="name-${player.id}" class="fw-bold fs-5">${player.name}</span>

                        <form id="form-${player.id}" class="d-none ajax-edit-form d-inline ms-2" action="/player/${player.id}" method="POST">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="text" name="name" value="${player.name}" class="form-control form-control-sm d-inline-block w-auto">
                            <button type="submit" class="btn btn-sm btn-success">Lưu</button>
                            <button type="button" class="btn btn-sm btn-secondary cancel-edit" data-id="${player.id}">Hủy</button>
                        </form>
                    </div>
                    <div class="ms-2 d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-warning edit-btn" data-id="${player.id}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form class="d-inline ajax-delete-form" action="/player/${player.id}" method="POST">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                ${membersHtml}
            </div>
        `;

                list.appendChild(li);
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
                                colorClass = 'text-white';
                            } else if (data.status === 'warning') {
                                colorClass = 'text-white';
                            } else {
                                colorClass = 'text-white';
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
