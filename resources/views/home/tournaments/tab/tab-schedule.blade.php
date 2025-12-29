@if ($tournament->status == 'open')
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar-range" style="font-size: 3rem; color: #444;"></i>
        <p class="mt-3">Lịch thi đấu sẽ hiển thị sau khi giải bắt đầu.</p>
    </div>
@else
    <div class="container-fluid mt-4">

        @php $totalRounds = $rounds->count(); @endphp

        @foreach ($rounds as $roundNumber => $matches)
            <div class="mb-5">
                <h5 class="text-info border-bottom border-secondary pb-2 mb-4 fw-bold text-uppercase">
                    @if ($roundNumber == $totalRounds)
                        @if ($matches->contains('match_index', 1))
                            Chung Kết & Hạng 3
                        @else
                            Chung Kết
                        @endif
                    @elseif ($roundNumber == $totalRounds - 1)
                        Bán Kết
                    @elseif ($roundNumber == $totalRounds - 2)
                        Tứ Kết
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
                            <div class="card bg-dark border-secondary h-100 shadow-sm schedule-card"
                                style="background-color: #1e1e1e !important;">
                                @if ($match->match_index == 1 && $loop->parent->last)
                                    <span class="badge bg-warning text-dark badge-corner-right">
                                        Tranh hạng 3
                                    </span>
                                @endif
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
                                            action="{{ route('matches.time.update', $match->id) }}" method="POST">
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

                                    <div class="text-center mt-2 d-flex flex-column align-items-center gap-1">
                                        @if ($match->score1 !== null)
                                            <span class="badge bg-secondary">Đã kết thúc</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Lịch thi đấu --}}
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
