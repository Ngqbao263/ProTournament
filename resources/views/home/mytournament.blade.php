@extends('layouts.app')

@section('content')
    <style>
        /* CSS riêng cho trang này */
        .my-tournaments-card {
            background: #1c1c1c;
            border: 1px solid #333;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }

        .my-tournaments-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .card-img-top {
            height: 160px;
            object-fit: cover;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        }

        .nav-pills .nav-link {
            background: transparent;
            border: 1px solid #444;
            color: #aaa;
            border-radius: 50px;
            margin-right: 10px;
            padding: 8px 24px;
            font-weight: 600;
        }

        .nav-pills .nav-link.active {
            background: #1b7c00;
            border-color: #1b7c00;
            color: #fff;
            box-shadow: 0 4px 10px rgba(27, 124, 0, 0.4);
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <div class="container py-5">
        <h2 class="text-white fw-bold mb-4 text-uppercase border-bottom border-secondary pb-3">
            <i class="bi bi-person-workspace me-2"></i>Giải đấu của tôi
        </h2>

        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-created-tab" data-bs-toggle="pill" data-bs-target="#pills-created"
                    type="button" role="tab">
                    <i class="bi bi-pencil-square me-2"></i>Giải tôi tạo ({{ $createdTournaments->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-joined-tab" data-bs-toggle="pill" data-bs-target="#pills-joined"
                    type="button" role="tab">
                    <i class="bi bi-controller me-2"></i>Giải tham gia ({{ $joinedTournaments->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">

            <div class="tab-pane fade show active" id="pills-created" role="tabpanel">
                @if ($createdTournaments->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-folder-x" style="font-size: 3rem;"></i>
                        <p class="mt-3">Bạn chưa tạo giải đấu nào.</p>
                        <a href="{{ route('tournaments.create') }}" class="btn btn-outline-success mt-2">Tạo giải ngay</a>
                    </div>
                @else
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach ($createdTournaments as $tournament)
                            <div class="col">
                                <a href="{{ route('tournament.show', $tournament->id) }}" class="text-decoration-none">
                                    <div class="card my-tournaments-card h-100 text-white">
                                        <div class="position-relative">
                                            @php
                                                $imgSrc = $tournament->thumbnail;
                                                if (!Str::startsWith($imgSrc, 'http')) {
                                                    $imgSrc = asset('storage/' . $imgSrc);
                                                    // Fallback nếu link lỗi
                                                    if (Str::contains($tournament->thumbnail, 'default')) {
                                                        $imgSrc = asset($tournament->thumbnail);
                                                    }
                                                }
                                            @endphp
                                            <img src="{{ $imgSrc }}" class="card-img-top"
                                                alt="{{ $tournament->name }}">

                                            {{-- Badge trạng thái --}}
                                            @if ($tournament->status == 'open')
                                                <span class="badge bg-success status-badge">Đăng ký</span>
                                            @elseif($tournament->status == 'started')
                                                <span class="badge bg-warning text-dark status-badge">Đang diễn ra</span>
                                            @elseif($tournament->status == 'completed')
                                                <span class="badge bg-danger status-badge">Kết thúc</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold text-truncate">{{ $tournament->name }}</h5>
                                            <p class="card-text text-muted small mb-2">
                                                <i class="bi bi-gamepad me-1"></i> {{ $tournament->game_name }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <small class="text-white-50"><i class="bi bi-people me-1"></i>
                                                    {{ $tournament->players->count() ?? 0 }}/{{ $tournament->max_player }}</small>
                                                <small class="text-white-50"><i class="bi bi-calendar3 me-1"></i>
                                                    {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="pills-joined" role="tabpanel">
                @if ($joinedTournaments->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-emoji-frown" style="font-size: 3rem;"></i>
                        <p class="mt-3">Bạn chưa tham gia giải đấu nào.</p>
                    </div>
                @else
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach ($joinedTournaments as $tournament)
                            <div class="col">
                                <a href="{{ route('tournament.show', $tournament->id) }}" class="text-decoration-none">
                                    <div class="card my-tournaments-card h-100 text-white" style="border-color: #444;">
                                        <div class="position-relative">
                                            @php
                                                $imgSrc = $tournament->thumbnail;
                                                if (!Str::startsWith($imgSrc, 'http')) {
                                                    $imgSrc = asset('storage/' . $imgSrc);
                                                    if (Str::contains($tournament->thumbnail, 'default')) {
                                                        $imgSrc = asset($tournament->thumbnail);
                                                    }
                                                }
                                            @endphp
                                            <img src="{{ $imgSrc }}" class="card-img-top"
                                                alt="{{ $tournament->name }}">

                                            {{-- Trạng thái tham gia của mình --}}
                                            @php
                                                $myPlayer = $tournament->players
                                                    ->where('user_id', auth()->id())
                                                    ->first();
                                                if (!$myPlayer) {
                                                    $myPlayer = $tournament->players
                                                        ->where('name', auth()->user()->name)
                                                        ->first();
                                                }
                                            @endphp

                                            @if ($myPlayer && $myPlayer->status == 'pending')
                                                <span class="badge bg-warning text-dark status-badge">Chờ duyệt</span>
                                            @elseif($myPlayer && $myPlayer->status == 'approved')
                                                <span class="badge bg-info text-dark status-badge">Đã tham gia</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold text-truncate">{{ $tournament->name }}</h5>
                                            <p class="card-text text-muted small mb-2">
                                                <i class="bi bi-person-circle me-1"></i> Tạo bởi:
                                                {{ $tournament->creator ? $tournament->creator->name : 'Admin' }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <small class="text-white-50"><i class="bi bi-people me-1"></i>
                                                    {{ $tournament->players->count() }}/{{ $tournament->max_player }}</small>
                                                <small class="text-white-50"><i class="bi bi-clock me-1"></i>
                                                    {{ $tournament->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
