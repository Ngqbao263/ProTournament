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
                {{ $tournament->max_player }} người chơi</p>
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

                {{-- Nút Danh sách người chơi nằm ở đây --}}
                <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                    @if (
                        $tournament->creator_id != auth()->id() &&
                            $tournament->status == 'open' &&
                            $tournament->players->where('status', 'approved')->count() < $tournament->max_player)
                        <form action="{{ route('tournament.join', $tournament->id) }}" method="POST"
                            class="ajax-join-form">
                            @csrf
                            <button class="btn btn-primary px-4" style="height: 40px">Xin tham gia</button>
                        </form>
                    @endif
                    <button type="button" class="btn btn-outline-light px-4" data-bs-toggle="modal"
                        data-bs-target="#playerModal">
                        <i class="bi bi-people-fill me-2"></i>Danh sách người chơi
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
                <div class="text-center py-5">
                    <i class="bi bi-info-circle me-2" style="font-size: 3rem; color: #444;"></i>
                    <p class="mt-3">
                        {!! $tournament->description ?? 'Chưa có mô tả cho giải đấu này.' !!}
                    </p>
                </div>


            </div>

            {{-- Sơ đồ thi đấu --}}
            <div class="tab-pane fade" id="bracket-content" role="tabpanel">
                @if ($tournament->status != 'open')
                    <div class="container-fluid">
                        @php $matchCounter = 1; @endphp
                        <div class="bracket-container" id="bracket-container">
                            <svg id="bracket-lines"></svg>
                            @foreach ($rounds as $roundNumber => $matches)
                                <div class="round-column">
                                    <div class="round-title">
                                        @if ($loop->last && $matches->contains('match_index', 1))
                                            Chung Kết & Hạng 3
                                        @elseif($loop->last)
                                            Chung Kết
                                        @else
                                            Vòng {{ $roundNumber }}
                                        @endif
                                    </div>
                                    <div class="match-list">
                                        @foreach ($matches as $match)
                                            <div class="match-card" id="match-{{ $match->id }}"
                                                data-match-id="{{ $match->id }}"
                                                data-round="{{ $match->round_number }}"
                                                data-index="{{ $match->match_index }}">
                                                <div class="player-row">
                                                    <span
                                                        class="player-name {{ $match->winner_id && $match->winner_id == $match->player1_id ? 'winner' : '' }} {{ $match->winner_id && $match->winner_id == $match->player2_id ? 'loser' : '' }}">
                                                        {{ $match->player1 ? $match->player1->name : '---' }}
                                                    </span>
                                                    <input type="number" class="score-input"
                                                        value="{{ $match->score1 }}" data-match-id="{{ $match->id }}"
                                                        data-player="1"
                                                        {{ !$match->player1 || !$match->player2 || $tournament->creator_id != auth()->id() ? 'disabled' : '' }}>
                                                </div>
                                                <div class="player-row">
                                                    <span
                                                        class="player-name {{ $match->winner_id && $match->winner_id == $match->player2_id ? 'winner' : '' }} {{ $match->winner_id && $match->winner_id == $match->player1_id ? 'loser' : '' }}">
                                                        {{ $match->player2 ? $match->player2->name : '---' }}
                                                    </span>
                                                    <input type="number" class="score-input"
                                                        value="{{ $match->score2 }}" data-match-id="{{ $match->id }}"
                                                        data-player="2"
                                                        {{ !$match->player1 || !$match->player2 || $tournament->creator_id != auth()->id() ? 'disabled' : '' }}>
                                                </div>
                                                <div class="text-center mt-1">
                                                    <small style="font-size: 10px; color: white">Trận
                                                        #{{ $matchCounter++ }}</small>
                                                    @if ($match->match_index == 1 && $loop->parent->last)
                                                        <span class="badge bg-warning text-dark"
                                                            style="font-size: 9px">Tranh hạng 3</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-diagram-3" style="font-size: 3rem; color: #444;"></i>
                        <p class="mt-3">Sơ đồ thi đấu sẽ hiển thị khi giải đấu bắt đầu.</p>
                    </div>
                @endif
            </div>

            {{-- Tab Lịch thi đấu --}}
            <div class="tab-pane fade" id="schedule-content" role="tabpanel">
                @if ($tournament->status == 'open')
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-range" style="font-size: 3rem;"></i>
                        <p class="mt-3">Lịch thi đấu sẽ hiển thị sau khi giải bắt đầu.</p>
                    </div>
                @else
                    <div class="container-fluid mt-4">
                        @foreach ($rounds as $roundNumber => $matches)
                            <div class="mb-5">
                                <h5 class="text-info border-bottom border-secondary pb-2 mb-4 fw-bold text-uppercase">
                                    @if ($loop->last && $matches->contains('match_index', 1))
                                        Chung Kết & Hạng 3
                                    @elseif($loop->last)
                                        Chung Kết
                                    @else
                                        Vòng {{ $roundNumber }}
                                    @endif
                                </h5>

                                <div class="row g-4">
                                    @php
                                        $sortedMatches = $matches->sortBy(function ($match) {
                                            // Nếu có giờ thi đấu thì lấy timestamp (số giây) để so sánh
                                            // Nếu chưa có giờ (null) thì gán số cực lớn (99999999999) để đẩy xuống cuối danh sách
                                            return $match->match_time ? $match->match_time->timestamp : 99999999999;
                                        });
                                    @endphp
                                    @foreach ($sortedMatches as $match)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card bg-dark border-secondary h-100 shadow-sm"
                                                style="background-color: #1e1e1e !important;">
                                                <div class="card-body">
                                                    {{-- Cặp đấu --}}
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        {{-- Player 1 --}}
                                                        <div class="text-end" style="width: 35%;">
                                                            <span
                                                                class="fw-bold {{ $match->winner_id && $match->winner_id == $match->player1_id ? 'text-white' : 'text-white' }} text-truncate d-block">
                                                                {{ $match->player1 ? $match->player1->name : '---' }}
                                                            </span>
                                                        </div>

                                                        {{-- Tỉ số hoặc VS --}}
                                                        <div class="text-center" style="width: 30%;">
                                                            @if ($match->score1 !== null && $match->score2 !== null)
                                                                <span class="fw-bold text-success px-2 py-1 rounded"
                                                                    style="background: #333; border: 1px solid #555;">
                                                                    {{ $match->score1 }} - {{ $match->score2 }}
                                                                </span>
                                                            @else
                                                                <span class="text-success fw-bold small">VS</span>
                                                            @endif
                                                        </div>

                                                        {{-- Player 2 --}}
                                                        <div class="text-start" style="width: 35%;">
                                                            <span
                                                                class="fw-bold {{ $match->winner_id && $match->winner_id == $match->player2_id ? 'text-white' : 'text-white' }} text-truncate d-block">
                                                                {{ $match->player2 ? $match->player2->name : '---' }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {{-- Khu vực chỉnh giờ (Chỉ hiện cho chủ giải) --}}
                                                    @if ($tournament->creator_id == auth()->id())
                                                        <form class="ajax-time-form d-flex gap-2 align-items-center"
                                                            action="{{ route('matches.time.update', $match->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="datetime-local" name="match_time"
                                                                class="form-control form-control-sm bg-dark text-white border-secondary"
                                                                value="{{ $match->match_time ? $match->match_time->format('Y-m-d\TH:i') : '' }}">
                                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                                title="Lưu giờ">
                                                                <i class="bi bi-check-lg"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        {{-- Hiển thị cho người xem --}}
                                                        <div class="text-center py-2 rounded"
                                                            style="background: rgba(255,255,255,0.05);">
                                                            @if ($match->match_time)
                                                                <div class="text-warning fw-bold">
                                                                    {{ $match->match_time->format('H:i') }}
                                                                </div>
                                                                <div class="text-white small">
                                                                    {{ $match->match_time->format('d/m/Y') }}
                                                                </div>
                                                            @else
                                                                <span class="text-white fst-italic small">Chưa xếp
                                                                    lịch</span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if ($match->score1 !== null)
                                                        <div class="text-center mt-2">
                                                            <span class="badge bg-secondary">Đã kết thúc</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tab Bảng xếp hạng --}}
            <div class="tab-pane fade" id="ranking-content" role="tabpanel">
                {{-- Logic tìm ra Top 3 --}}
                @php
                    $finalRound = $rounds->last();
                    $finalMatch = $finalRound ? $finalRound->firstWhere('match_index', 0) : null;
                    $thirdMatch = $finalRound ? $finalRound->firstWhere('match_index', 1) : null;

                    $champion = $finalMatch && $finalMatch->winner_id ? $finalMatch->winner : null;
                    $runnerUp =
                        $finalMatch && $finalMatch->winner_id
                            ? ($finalMatch->winner_id == $finalMatch->player1_id
                                ? $finalMatch->player2
                                : $finalMatch->player1)
                            : null;
                    $thirdPlace = $thirdMatch && $thirdMatch->winner_id ? $thirdMatch->winner : null;
                @endphp

                {{-- Bục vinh danh --}}
                @if ($champion)
                    <div class="podium-section text-center mb-5 animate__animated animate__fadeInDown">
                        <h2 class="fw-bold text-uppercase mb-4"
                            style="letter-spacing: 2px; color: #f1c40f; text-shadow: 0 0 10px rgba(241, 196, 15, 0.5);">
                            <i class="bi bi-trophy-fill me-2"></i>Kết Quả Chung Cuộc
                        </h2>
                        <div class="row justify-content-center align-items-end gx-4">
                            <div class="col-4 col-md-3 order-1">
                                <div class="podium-card silver">
                                    <div class="medal">🥈</div>
                                    <div class="player-avatar"><span
                                            class="fs-1 fw-bold">{{ substr($runnerUp->name ?? '?', 0, 1) }}</span></div>
                                    <div class="player-name">{{ $runnerUp->name ?? 'Á Quân' }}</div>
                                    <div class="rank-title">Hạng Nhì</div>
                                </div>
                            </div>
                            <div class="col-4 col-md-4 order-2">
                                <div class="podium-card gold">
                                    <div class="medal">🥇</div>
                                    <div class="crown"><i class="bi bi-crown-fill"></i></div>
                                    <div class="player-avatar"><span
                                            class="fs-1 fw-bold">{{ substr($champion->name, 0, 1) }}</span></div>
                                    <div class="player-name">{{ $champion->name }}</div>
                                    <div class="rank-title">VÔ ĐỊCH</div>
                                </div>
                            </div>
                            <div class="col-4 col-md-3 order-3">
                                <div class="podium-card bronze">
                                    <div class="medal">🥉</div>
                                    <div class="player-avatar"><span
                                            class="fs-1 fw-bold">{{ substr($thirdPlace->name ?? '?', 0, 1) }}</span></div>
                                    <div class="player-name">{{ $thirdPlace->name ?? 'Hạng 3' }}</div>
                                    <div class="rank-title">Hạng Ba</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="container-fluid mt-4">
                    {{-- Nếu giải chưa bắt đầu thì báo chưa có dữ liệu --}}
                    @if ($tournament->status == 'open')
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart-line" style="font-size: 3rem;"></i>
                            <p class="mt-3">Bảng xếp hạng sẽ cập nhật khi giải đấu bắt đầu.</p>
                        </div>
                    @else
                        {{-- Nếu giải đã chạy hoặc kết thúc thì hiện Bảng --}}
                        <div class="card bg-dark border-secondary shadow">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover mb-0 align-middle">
                                        <thead class="bg-secondary text-uppercase small text-white">
                                            <tr>
                                                <th class="text-center py-3" style="width: 60px;">#</th>
                                                <th class="py-3">Người chơi</th>
                                                <th class="text-center py-3">Thành tích</th>
                                                <th class="text-center py-3">Thắng</th>
                                                <th class="text-center py-3">Hiệu số</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Duyệt qua biến $rankings được truyền từ Controller --}}
                                            @foreach ($rankings as $rank)
                                                <tr class="{{ $loop->first ? 'table-active border-warning' : '' }}">
                                                    {{-- Cột Thứ hạng --}}
                                                    <td class="text-center fw-bold fs-5">
                                                        @if (isset($rank['medal']) && $rank['medal'])
                                                            {{ $rank['medal'] }}
                                                        @else
                                                            <span class="text-secondary">{{ $loop->iteration }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Cột Người chơi --}}
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-circle me-3 bg-secondary d-flex align-items-center justify-content-center rounded-circle"
                                                                style="width: 35px; height: 35px; font-weight:bold;">
                                                                {{ substr($rank['player']->name, 0, 1) }}
                                                            </div>
                                                            <span class="fw-bold">{{ $rank['player']->name }}</span>
                                                        </div>
                                                    </td>

                                                    {{-- Cột Danh hiệu (Vô địch, Á quân...) --}}
                                                    <td class="text-center">
                                                        {!! $rank['rank_label'] !!}
                                                    </td>

                                                    {{-- Cột Số trận thắng --}}
                                                    <td class="text-center text-success fw-bold">
                                                        {{ $rank['wins'] }}
                                                    </td>

                                                    {{-- Cột Hiệu số --}}
                                                    <td class="text-center text-info">
                                                        {{ $rank['score_diff'] > 0 ? '+' : '' }}{{ $rank['score_diff'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modal Danh sách người chơi --}}
        <div class="modal fade" id="playerModal" tabindex="-1" aria-labelledby="playerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-dark text-white border-secondary shadow-lg">

                    <div class="modal-header text-white">
                        <h5 class="modal-title" id="playerModalLabel">
                            <i class="bi bi-people-fill me-2"></i>Danh sách người chơi
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
                                        <ul class="list-group list-group-flush">
                                            @foreach ($tournament->players->where('status', 'pending') as $player)
                                                <li
                                                    class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">
                                                    {{ $player->name }}
                                                    <form action="{{ route('player.approve', $player->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success">Duyệt</button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div id="add-player-section" class="mb-4">
                                <h5 class="fw-semibold text-info mb-3">
                                    <i class="bi bi-plus-circle me-2"></i>Thêm người chơi
                                </h5>
                                <form action="{{ route('tournament.addPlayer', $tournament->id) }}" method="POST"
                                    class="ajax-add-player-form {{ $tournament->players->where('status', 'approved')->count() >= $tournament->max_player ? 'd-none' : '' }}">
                                    @csrf
                                    <div class="input-group">
                                        <input type="text" name="name"
                                            class="form-control bg-dark text-white border-secondary"
                                            placeholder="Nhập tên người chơi..." required>
                                        <button class="btn btn-success">Thêm</button>
                                    </div>
                                </form>

                                <p class="text-warning fst-italic {{ $tournament->players->where('status', 'approved')->count() < $tournament->max_player ? 'd-none' : '' }}"
                                    id="full-player-text">
                                    Giải đấu đã đủ người chơi ({{ $tournament->max_player }}).
                                </p>
                            </div>
                        @endif

                        <div>
                            <h5 class="fw-semibold text-success mb-3">
                                <i class="bi bi-check-circle me-2"></i>Người chơi đã duyệt
                            </h5>
                            <div class="player-list-scroll">
                                <ul class="list-group list-group-flush" id="approved-player-list">
                                    @forelse ($tournament->players->where('status', 'approved') as $player)
                                        <li
                                            class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <span
                                                    class="me-3 fw-bold text-success player-stt">{{ $loop->iteration }}.</span>
                                                <span id="name-{{ $player->id }}">{{ $player->name }}</span>
                                                <form id="form-{{ $player->id }}"
                                                    class="d-none ajax-edit-form d-inline"
                                                    action="{{ route('player.update', $player->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <input type="text" name="name" value="{{ $player->name }}"
                                                        class="form-control form-control-sm d-inline-block w-auto">
                                                    <button type="submit" class="btn btn-sm btn-success">Lưu</button>
                                                    <button type="button" class="btn btn-sm btn-secondary cancel-edit"
                                                        data-id="{{ $player->id }}">Hủy</button>
                                                </form>
                                            </div>
                                            @if ($tournament->creator_id == auth()->id() && $tournament->status == 'open')
                                                <div class="ms-2 d-flex align-items-center gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-warning edit-btn"
                                                        data-id="{{ $player->id }}"><i
                                                            class="bi bi-pencil"></i></button>
                                                    <form class="d-inline ajax-delete-form"
                                                        action="{{ route('player.delete', $player->id) }}"
                                                        method="POST">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i
                                                                class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            @endif
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

    {{-- === MODAL THÔNG BÁO KẾT QUẢ ĐĂNG KÝ === --}}
    <div class="modal fade" id="joinResultModal" tabindex="-1" aria-labelledby="joinResultModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header">
                    <h5 class="modal-title" id="joinResultModalLabel">
                        <i class="bi bi-info-circle me-2"></i>Thông báo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                {{-- Nơi hiển thị nội dung thông báo --}}
                <div class="modal-body" id="joinResultMessage"></div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Sửa tên người chơi --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const maxPlayer = {{ $tournament->max_player }};
            const playerList = document.getElementById('approved-player-list');
            const addForm = document.querySelector('.ajax-add-player-form');
            const addSection = document.getElementById('add-player-section');
            const fullText = document.getElementById('full-player-text');

            function updateAddSection() {
                const count = playerList.querySelectorAll('li').length;
                if (count >= maxPlayer) {
                    if (addForm) addForm.classList.add('d-none');
                    if (fullText) fullText.classList.remove('d-none');
                } else {
                    if (addForm) addForm.classList.remove('d-none');
                    if (fullText) fullText.classList.add('d-none');
                }
            }

            function updatePlayerIndexes() {
                document.querySelectorAll("#approved-player-list li").forEach((li, index) => {
                    const sttSpan = li.querySelector(".player-stt");
                    if (sttSpan) {
                        sttSpan.textContent = (index + 1) + ".";
                    }
                });
            }

            function attachEvents(container) {
                // Sửa
                container.querySelectorAll('.edit-btn').forEach(btn => {
                    btn.onclick = () => {
                        const id = btn.dataset.id;
                        document.getElementById(`name-${id}`).classList.add('d-none');
                        document.getElementById(`form-${id}`).classList.remove('d-none');
                        btn.classList.add('d-none');
                    };
                });

                // Hủy sửa
                container.querySelectorAll('.cancel-edit').forEach(btn => {
                    btn.onclick = () => {
                        const id = btn.dataset.id;
                        document.getElementById(`name-${id}`).classList.remove('d-none');
                        document.getElementById(`form-${id}`).classList.add('d-none');
                        document.querySelector(`.edit-btn[data-id="${id}"]`).classList.remove('d-none');
                    };
                });

                // Form sửa
                container.querySelectorAll('.ajax-edit-form').forEach(form => {
                    form.onsubmit = async (e) => {
                        e.preventDefault();
                        const id = form.id.replace('form-', '');
                        const input = form.querySelector('input[name="name"]');
                        const formData = new FormData(form);
                        formData.append('_method', 'PUT');

                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')
                                    .value
                            },
                            body: formData
                        });

                        if (res.ok) {
                            document.getElementById(`name-${id}`).textContent = input.value;
                            document.getElementById(`name-${id}`).classList.remove('d-none');
                            form.classList.add('d-none');
                            document.querySelector(`.edit-btn[data-id="${id}"]`).classList.remove(
                                'd-none');
                        } else alert('Cập nhật thất bại!');
                    };
                });

                // Form xóa
                container.querySelectorAll('.ajax-delete-form').forEach(form => {
                    form.onsubmit = async (e) => {
                        e.preventDefault();
                        const li = form.closest('li');
                        const formData = new FormData(form);
                        formData.append('_method', 'DELETE');

                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')
                                    .value
                            },
                            body: formData
                        });

                        if (res.ok) {
                            li.remove();
                            updateAddSection();
                            updatePlayerIndexes();
                        } else alert('Xóa thất bại!');
                    };
                });
            }

            attachEvents(document);

            // Thêm người chơi
            if (addForm) {
                addForm.onsubmit = async (e) => {
                    e.preventDefault();
                    const input = addForm.querySelector('input[name="name"]');
                    const res = await fetch(addForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': addForm.querySelector('input[name="_token"]').value
                        },
                        body: new FormData(addForm)
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const li = document.createElement('li');
                        li.className =
                            'list-group-item bg-dark text-white d-flex justify-content-between align-items-center';
                        li.innerHTML = `
                            <div class="flex-grow-1">
                                <span class="me-3 fw-bold text-success player-stt"></span>
                                <span id="name-${data.id}">${data.name}</span>
                                <form id="form-${data.id}" class="d-none ajax-edit-form d-inline" action="/player/${data.id}" method="POST">
                                    <input type="hidden" name="_token" value="${addForm.querySelector('input[name="_token"]').value}">
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
                                    <input type="hidden" name="_token" value="${addForm.querySelector('input[name="_token"]').value}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        `;
                        playerList.appendChild(li);
                        attachEvents(li);
                        input.value = '';
                        updateAddSection();
                        updatePlayerIndexes();
                    } else alert('Thêm thất bại!');
                };
            }

            updateAddSection();
        });
    </script>

    <script>
        // Xử lý xin tham gia giải đấu
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.score-input');

            inputs.forEach(input => {
                input.addEventListener('blur', function() { // Sự kiện khi nhập xong và click ra ngoài
                    const matchId = this.dataset.matchId;
                    const matchCard = document.getElementById(`match-${matchId}`);

                    // Tìm 2 ô input trong cùng 1 thẻ match-card
                    const score1Input = matchCard.querySelector('input[data-player="1"]');
                    const score2Input = matchCard.querySelector('input[data-player="2"]');

                    const score1 = score1Input.value;
                    const score2 = score2Input.value;

                    // Chỉ gửi request khi CẢ 2 ô đều có dữ liệu
                    if (score1 !== '' && score2 !== '') {
                        saveMatchResult(matchId, score1, score2, this);
                    }
                });
            });

            async function saveMatchResult(matchId, score1, score2, inputElement) {
                const currentCard = document.getElementById(`match-${matchId}`);
                const currentRound = parseInt(currentCard.dataset.round);
                const currentIndex = parseInt(currentCard.dataset.index);

                try {
                    const response = await fetch(`/matches/${matchId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            score1: parseInt(score1),
                            score2: parseInt(score2)
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // === 1. CẬP NHẬT MÀU SẮC NGAY TẠI TRẬN VỪA NHẬP ===
                        // Tìm 2 span tên người chơi
                        const p1Span = currentCard.querySelector('input[data-player="1"]')
                            .previousElementSibling;
                        const p2Span = currentCard.querySelector('input[data-player="2"]')
                            .previousElementSibling;

                        // Reset class cũ
                        p1Span.classList.remove('winner', 'loser');
                        p2Span.classList.remove('winner', 'loser');

                        // Gán class mới dựa trên winner_id trả về
                        // data.winner_id là ID của người thắng trong DB
                        // Chúng ta so sánh data.winner_name với nội dung text để biết ai thắng (hoặc dùng logic điểm số)
                        if (parseInt(score1) > parseInt(score2)) {
                            p1Span.classList.add('winner');
                            p2Span.classList.add('loser');
                        } else if (parseInt(score2) > parseInt(score1)) {
                            p2Span.classList.add('winner');
                            p1Span.classList.add('loser');
                        }

                        // === 2. XỬ LÝ NGƯỜI THẮNG (VÀO VÒNG TRONG) ===
                        const nextRound = currentRound + 1;
                        const nextIndex = Math.floor(currentIndex / 2);
                        const nextCard = document.querySelector(
                            `.match-card[data-round="${nextRound}"][data-index="${nextIndex}"]`);

                        if (nextCard && data.winner_name) {
                            const targetPlayerSlot = (currentIndex % 2 === 0) ? 1 : 2;
                            const opponentSlot = (targetPlayerSlot === 1) ? 2 : 1;

                            const targetInput = nextCard.querySelector(
                                `input[data-player="${targetPlayerSlot}"]`);
                            const targetNameSpan = targetInput.previousElementSibling;
                            const opponentInput = nextCard.querySelector(
                                `input[data-player="${opponentSlot}"]`);
                            const opponentNameSpan = opponentInput.previousElementSibling;

                            targetNameSpan.textContent = data.winner_name;
                            targetNameSpan.style.color = '#00ff7f';
                            setTimeout(() => {
                                targetNameSpan.style.color = '';
                            }, 1000);

                            if (opponentNameSpan.textContent.trim() !== '---') {
                                targetInput.disabled = false;
                                opponentInput.disabled = false;
                            } else {
                                targetInput.disabled = true;
                            }
                        }

                        // === 3. XỬ LÝ NGƯỜI THUA (VÀO TRANH HẠNG 3) ===
                        // Kiểm tra xem server có trả về tên người thua không
                        if (data.loser_name) {
                            const thirdPlaceCard = document.querySelector(
                                `.match-card[data-round="${nextRound}"][data-index="1"]`);

                            if (thirdPlaceCard) {
                                // Logic slot cho hạng 3 tương tự: Trận bán kết 1 (index 0) vào slot 1, BK 2 (index 1) vào slot 2
                                const loserSlot = (currentIndex % 2 === 0) ? 1 : 2;
                                const loserOpponentSlot = (loserSlot === 1) ? 2 : 1;

                                const loserInput = thirdPlaceCard.querySelector(
                                    `input[data-player="${loserSlot}"]`);
                                const loserNameSpan = loserInput.previousElementSibling;
                                const opponentInput = thirdPlaceCard.querySelector(
                                    `input[data-player="${loserOpponentSlot}"]`);
                                const opponentNameSpan = opponentInput.previousElementSibling;

                                loserNameSpan.textContent = data.loser_name;
                                loserNameSpan.style.color = '#ffc107'; // Màu vàng cho khác biệt
                                setTimeout(() => {
                                    loserNameSpan.style.color = '';
                                }, 1000);

                                if (opponentNameSpan.textContent.trim() !== '---') {
                                    loserInput.disabled = false;
                                    opponentInput.disabled = false;
                                } else {
                                    loserInput.disabled = true;
                                }
                            }
                        }

                        // === 4. XỬ LÝ PODIUM (NẾU CÓ DỮ LIỆU) ===
                        if (data.podium) {
                            // Điền dữ liệu vào bục
                            document.getElementById('podium-gold-name').textContent = data.podium.gold;
                            document.getElementById('podium-silver-name').textContent = data.podium.silver;
                            document.getElementById('podium-bronze-name').textContent = data.podium.bronze;

                            document.getElementById('podium-gold-char').textContent = data.podium.gold_initial;
                            document.getElementById('podium-silver-char').textContent = data.podium
                                .silver_initial;
                            document.getElementById('podium-bronze-char').textContent = data.podium
                                .bronze_initial;

                            // Hiện bục lên
                            const podiumArea = document.querySelector('.podium-section');
                            if (podiumArea) {
                                podiumArea.classList.remove('d-none');
                                podiumArea.scrollIntoView({
                                    behavior: 'smooth'
                                });
                            } else {
                                // Nếu bục chưa có trong DOM (do load lần đầu ẩn), reload để hiện
                                window.location.reload();
                            }
                        }

                    } else {
                        alert('Lỗi khi lưu kết quả!');
                    }
                } catch (error) {
                    console.error(error);
                }
            }
        });
    </script>

    {{-- VẼ NHÁNH --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function drawBracketLines() {
                const container = document.getElementById('bracket-container');
                const svg = document.getElementById('bracket-lines');

                if (!container || !svg) return;

                // Reset SVG
                svg.innerHTML = '';
                svg.setAttribute('width', container.scrollWidth);
                svg.setAttribute('height', container.scrollHeight);

                const matches = document.querySelectorAll('.match-card');

                matches.forEach(match => {
                    const round = parseInt(match.dataset.round);
                    const index = parseInt(match.dataset.index);

                    // Tìm trận đấu tiếp theo: Vòng sau, Vị trí index / 2
                    const nextRound = round + 1;
                    const nextIndex = Math.floor(index / 2);

                    // Tìm thẻ HTML của trận tiếp theo dựa trên data-round và data-index
                    const nextMatch = document.querySelector(
                        `.match-card[data-round="${nextRound}"][data-index="${nextIndex}"]`);

                    if (nextMatch) {
                        const startRect = match.getBoundingClientRect();
                        const endRect = nextMatch.getBoundingClientRect();
                        const containerRect = container.getBoundingClientRect();

                        // Tính tọa độ (trừ đi scroll của container để chính xác)
                        const scrollLeft = container.scrollLeft;
                        const scrollTop = container.scrollTop; // Thường là 0

                        // Điểm đầu: Giữa cạnh Phải thẻ trước
                        const x1 = (startRect.right - containerRect.left) + scrollLeft;
                        const y1 = (startRect.top + startRect.height / 2 - containerRect.top) + scrollTop;

                        // Điểm cuối: Giữa cạnh Trái thẻ sau
                        const x2 = (endRect.left - containerRect.left) + scrollLeft;
                        const y2 = (endRect.top + endRect.height / 2 - containerRect.top) + scrollTop;

                        // Điểm giữa để bẻ cua
                        const xMid = x1 + (x2 - x1) / 2;

                        // Vẽ dây: Đi thẳng -> Bẻ vuông góc -> Đi thẳng
                        const pathStr = `M ${x1} ${y1} L ${xMid} ${y1} L ${xMid} ${y2} L ${x2} ${y2}`;

                        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                        path.setAttribute("d", pathStr);
                        path.setAttribute("stroke", "#555"); // Màu dây
                        path.setAttribute("stroke-width", "2");
                        path.setAttribute("fill", "none");

                        svg.appendChild(path);
                    }
                });
            }

            // Vẽ ngay khi tải xong
            setTimeout(drawBracketLines, 100);

            // Vẽ lại khi thay đổi kích thước màn hình
            window.addEventListener('resize', drawBracketLines);

            // Vẽ lại khi scroll (đôi khi cần thiết trên mobile)
            document.getElementById('bracket-container').addEventListener('scroll', drawBracketLines);

            // --- SỰ KIỆN QUAN TRỌNG: VẼ LẠI KHI CHUYỂN TAB ---
            const bracketTabBtn = document.getElementById('bracket-tab');
            if (bracketTabBtn) {
                bracketTabBtn.addEventListener('shown.bs.tab', function() {
                    // Khi tab Bảng đấu hiện ra hoàn toàn -> Gọi hàm vẽ dây
                    setTimeout(drawBracketLines, 50); // Delay 50ms để giao diện load xong
                });
            }

            // Vẽ lại khi xoay màn hình điện thoại
            // window.addEventListener('orientationchange', () => {
            //     setTimeout(drawBracketLines, 200); // Delay chút để giao diện xoay xong mới vẽ
            // });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Khôi phục Tab đã lưu
            const activeTabId = localStorage.getItem('activeTournamentTab');
            if (activeTabId) {
                const tabTrigger = document.querySelector(`#${activeTabId}`);
                if (tabTrigger) {
                    const tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }

            // 2. Lưu lại Tab khi bấm chuyển
            const tabLinks = document.querySelectorAll('button[data-bs-toggle="pill"]');
            tabLinks.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    localStorage.setItem('activeTournamentTab', event.target.id);
                });
            });
        });
    </script>

    <script>
        // Xử lý lưu lịch thi đấu
        const timeForms = document.querySelectorAll('.ajax-time-form');
        timeForms.forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const btn = form.querySelector('button');
                const originalContent = btn.innerHTML;

                // Hiệu ứng loading
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: new FormData(form)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Báo thành công
                        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                        btn.classList.remove('btn-outline-success');
                        btn.classList.add('btn-success');

                        setTimeout(() => {
                            btn.innerHTML = originalContent;
                            btn.disabled = false;
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-outline-success');
                        }, 2000);
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không thể lưu'));
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                    }
                } catch (error) {
                    console.error(error);
                    alert('Lỗi kết nối!');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            });
        });
    </script>
@endsection
