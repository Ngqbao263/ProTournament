@if ($tournament->status != 'open')
    <div class="container-fluid">
        @php $matchCounter = 1; @endphp
        <div class="bracket-container" id="bracket-container">
            <svg id="bracket-lines"></svg>

            @php $totalRounds = $rounds->count(); @endphp

            @foreach ($rounds as $roundNumber => $matches)
                <div class="round-column">
                    <div class="round-title">
                        @if ($roundNumber == $totalRounds)
                            {{-- Vòng cuối cùng: Chung kết --}}
                            @if ($matches->contains('match_index', 1))
                                Chung Kết & Hạng 3
                            @else
                                Chung Kết
                            @endif
                        @elseif ($roundNumber == $totalRounds - 1)
                            {{-- Kế cuối: Bán kết --}}
                            Bán Kết
                        @elseif ($roundNumber == $totalRounds - 2)
                            {{-- Kế của kế cuối: Tứ kết --}}
                            Tứ Kết
                        @else
                            {{-- Còn lại --}}
                            Vòng {{ $roundNumber }}
                        @endif
                    </div>
                    <div class="match-list">
                        @foreach ($matches as $match)
                            <div class="match-card" id="match-{{ $match->id }}" data-match-id="{{ $match->id }}"
                                data-round="{{ $match->round_number }}" data-index="{{ $match->match_index }}">

                                <div class="player-row">
                                    <span
                                        class="player-name {{ $match->winner_id && $match->winner_id == $match->player1_id ? 'winner' : '' }} {{ $match->winner_id && $match->winner_id == $match->player2_id ? 'loser' : '' }}">
                                        {{ $match->player1 ? $match->player1->name : '---' }}
                                    </span>
                                    <input type="number" class="score-input" value="{{ $match->score1 }}"
                                        data-match-id="{{ $match->id }}" data-player="1"
                                        {{ !$match->player1 || !$match->player2 || $tournament->creator_id != auth()->id() ? 'disabled' : '' }}>
                                </div>
                                <div class="player-row">
                                    <span
                                        class="player-name {{ $match->winner_id && $match->winner_id == $match->player2_id ? 'winner' : '' }} {{ $match->winner_id && $match->winner_id == $match->player1_id ? 'loser' : '' }}">
                                        {{ $match->player2 ? $match->player2->name : '---' }}
                                    </span>
                                    <input type="number" class="score-input" value="{{ $match->score2 }}"
                                        data-match-id="{{ $match->id }}" data-player="2"
                                        {{ !$match->player1 || !$match->player2 || $tournament->creator_id != auth()->id() ? 'disabled' : '' }}>
                                </div>
                                <div class="text-center mt-1">
                                    <small style="font-size: 10px; color: white">Trận
                                        #{{ $matchCounter++ }}</small>
                                    @if ($match->match_index == 1 && $loop->parent->last)
                                        <span class="badge bg-warning text-dark" style="font-size: 9px">Tranh hạng
                                            3</span>
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


{{-- Cập nhật kết quả --}}
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


