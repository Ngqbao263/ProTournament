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
                    <div class="player-name">{{ $runnerUp->name ?? 'Á Quân' }}</div>
                    <div class="rank-title">Hạng Nhì</div>
                </div>
            </div>
            <div class="col-4 col-md-4 order-2">
                <div class="podium-card gold">
                    <div class="medal">🥇</div>
                    <div class="crown"><i class="bi bi-crown-fill"></i></div>
                    <div class="player-name">{{ $champion->name }}</div>
                    <div class="rank-title">VÔ ĐỊCH</div>
                </div>
            </div>
            <div class="col-4 col-md-3 order-3">
                <div class="podium-card bronze">
                    <div class="medal">🥉</div>
                    <div class="player-name">{{ $thirdPlace->name ?? 'Hạng 3' }}</div>
                    <div class="rank-title">Hạng Ba</div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="container-fluid">
    {{-- Nếu giải chưa bắt đầu thì báo chưa có dữ liệu --}}
    @if ($tournament->status == 'open')
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bar-chart-line" style="font-size: 3rem; color: #444"></i>
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
