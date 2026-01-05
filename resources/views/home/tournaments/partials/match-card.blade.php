<div class="match-card" id="match-{{ $match->id }}" data-match-id="{{ $match->id }}"
    data-round="{{ $match->round_number }}" data-index="{{ $match->match_index }}" {{-- QUAN TRỌNG: Dùng bracketType truyền vào hoặc lấy từ group trong DB --}}
    data-group="{{ $bracketType ?? ($match->group ?? 'single') }}">

    {{-- Dòng Người chơi 1 --}}
    <div class="player-row">
        <span
            class="player-name {{ $match->winner_id && $match->winner_id == $match->player1_id ? 'winner' : '' }} {{ $match->winner_id && $match->winner_id == $match->player2_id ? 'loser' : '' }}">
            {{ $match->player1 ? $match->player1->name : '---' }}
        </span>
        <input type="number" class="score-input" value="{{ $match->score1 }}" data-match-id="{{ $match->id }}"
            data-player="1"
            {{ !$match->player1 || !$match->player2 || $tournament->creator_id != auth()->id() ? 'disabled' : '' }}>
    </div>

    {{-- Dòng Người chơi 2 --}}
    <div class="player-row">
        <span
            class="player-name {{ $match->winner_id && $match->winner_id == $match->player2_id ? 'winner' : '' }} {{ $match->winner_id && $match->winner_id == $match->player1_id ? 'loser' : '' }}">
            {{ $match->player2 ? $match->player2->name : '---' }}
        </span>
        <input type="number" class="score-input" value="{{ $match->score2 }}" data-match-id="{{ $match->id }}"
            data-player="2"
            {{ !$match->player1 || !$match->player2 || $tournament->creator_id != auth()->id() ? 'disabled' : '' }}>
    </div>

    {{-- Phần Footer hiển thị số trận --}}
    <div class="text-center mt-1">
        <small style="font-size: 10px; color: white">Trận #{{ $matchCounter }}</small>

        {{-- Logic hiển thị nhãn phụ cho Nhánh thua hoặc Chung kết --}}
        {{-- @if (isset($bracketType))
            @if ($bracketType == 'loser')
                <span class="badge bg-secondary" style="font-size: 8px">Nhánh thua</span>
            @elseif ($bracketType == 'final')
                <span class="badge bg-warning text-dark" style="font-size: 8px">Chung kết</span>
            @endif
        @endif --}}

        {{-- Logic hiển thị Tranh hạng 3 của Loại trực tiếp (nếu dùng chung file này) --}}
        @if (
            !isset($bracketType) &&
                $match->match_index == 1 &&
                $match->round_number == $tournament->matches->max('round_number'))
            <span class="badge bg-warning text-dark" style="font-size: 9px">Tranh hạng 3</span>
        @endif
    </div>
</div>
