@if ($tournament->status != 'open')
    <div class="container-fluid pb-5">
        @php $matchCounter = 1; @endphp

        {{-- CSS CỦA BẠN SẼ TỰ ĐỘNG ÁP DỤNG VÌ CHÚNG TA DÙNG ĐÚNG CLASS --}}

        {{-- KIỂM TRA THỂ THỨC --}}
        @if ($tournament->type == 'double_elimination')

            {{-- === 1. NHÁNH THẮNG (WINNER BRACKET) === --}}
            <div class="d-flex align-items-center mb-3 mt-4">
                <h4 class="text-white fw-bold m-0 text-uppercase">Nhánh Thắng</h4>
            </div>

            {{-- Dùng đúng class "bracket-container" để nhận CSS flex, gap, scroll --}}
            <div class="bracket-container" id="bracket-winner">
                <svg class="bracket-lines"></svg>

                @foreach ($tournament->matches->where('group', 'winner')->sortBy('match_index')->groupBy('round_number') as $roundNumber => $matches)
                    <div class="round-column">
                        <div class="round-title">Vòng {{ $roundNumber }}</div>
                        <div class="match-list">
                            @foreach ($matches as $match)
                                @include('home.tournaments.partials.match-card', [
                                    'match' => $match,
                                    'matchCounter' => $matchCounter++,
                                    'bracketType' => 'winner',
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <hr>

            {{-- === 2. NHÁNH THUA (LOSER BRACKET) === --}}
            <div class="d-flex align-items-center mb-3">
                <h4 class="text-warning fw-bold m-0 text-uppercase">Nhánh Thua</h4>
            </div>

            <div class="bracket-container mb-5" id="bracket-loser">
                <svg class="bracket-lines"></svg>

                @foreach ($tournament->matches->where('group', 'loser')->sortBy('match_index')->groupBy('round_number') as $roundNumber => $matches)
                    <div class="round-column">
                        <div class="round-title">Vòng {{ $roundNumber }}</div>
                        <div class="match-list">
                            @foreach ($matches as $match)
                                @include('home.tournaments.partials.match-card', [
                                    'match' => $match,
                                    'matchCounter' => $matchCounter++,
                                    'bracketType' => 'loser',
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <hr>

            {{-- === 3. CHUNG KẾT TỔNG (GRAND FINAL) === --}}
            <div class="d-flex align-items-center mb-3 justify-content-center">
                <h4 class="text-success fw-bold m-0 text-uppercase">Chung Kết Tổng</h4>
            </div>

            {{-- Class justify-content-center để căn giữa trận chung kết --}}
            <div class="bracket-container justify-content-center" id="bracket-final">
                @foreach ($tournament->matches->where('group', 'final') as $match)
                    <div class="round-column">
                        <div class="match-list justify-content-center">
                            @include('home.tournaments.partials.match-card', [
                                'match' => $match,
                                'matchCounter' => $matchCounter++,
                                'bracketType' => 'final',
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- === GIAO DIỆN LOẠI TRỰC TIẾP (GIỮ NGUYÊN) === --}}
            <div class="bracket-container" id="bracket-container">
                <svg id="bracket-lines"></svg>
                @php $totalRounds = $rounds->count(); @endphp

                @foreach ($rounds as $roundNumber => $matches)
                    <div class="round-column">
                        <div class="round-title">
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
                        </div>
                        <div class="match-list">
                            @foreach ($matches as $match)
                                @include('home.tournaments.partials.match-card', [
                                    'match' => $match,
                                    'matchCounter' => $matchCounter++,
                                    'bracketType' => 'single',
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
                    if (data.winner_name && data.winner_info) {
                        const wGroup = data.winner_info.group; // winner, loser, hoặc final
                        const wRound = data.winner_info.round_number;
                        const wIndex = data.winner_info.match_index;
                        const wSlot = data.winner_info.slot;

                        // Tìm thẻ Card chính xác dựa trên Group, Round, Index
                        const nextCard = document.querySelector(
                            `.match-card[data-group="${wGroup}"][data-round="${wRound}"][data-index="${wIndex}"]`
                        );

                        if (nextCard) {
                            const targetInput = nextCard.querySelector(`input[data-player="${wSlot}"]`);
                            const targetNameSpan = targetInput.previousElementSibling;

                            // Tìm đối thủ để check mở khóa
                            const opponentSlot = (wSlot === 1) ? 2 : 1;
                            const opponentInput = nextCard.querySelector(
                                `input[data-player="${opponentSlot}"]`);
                            const opponentNameSpan = opponentInput.previousElementSibling;

                            // Điền tên
                            targetNameSpan.textContent = data.winner_name;
                            targetNameSpan.style.color = '#00ff7f';
                            setTimeout(() => {
                                targetNameSpan.style.color = '';
                            }, 1000);

                            // Mở khóa nếu đối thủ đã có mặt
                            if (opponentNameSpan.textContent.trim() !== '---' && opponentNameSpan
                                .textContent.trim() !== '') {
                                targetInput.disabled = false;
                                opponentInput.disabled = false;
                            } else {
                                targetInput.disabled = true;
                            }
                        }
                    }

                    // TRƯỜNG HỢP B: Logic cũ (Dành cho giải Single Elimination - Dự phòng)
                    else if (data.winner_name) {
                        const nextRound = currentRound + 1;
                        const nextIndex = Math.floor(currentIndex / 2);
                        // Tìm đại trong group hiện tại hoặc single
                        const currentGroup = currentCard.dataset.group || 'single';

                        const nextCard = document.querySelector(
                            `.match-card[data-group="${currentGroup}"][data-round="${nextRound}"][data-index="${nextIndex}"]`
                        );

                        if (nextCard) {
                            const targetPlayerSlot = (currentIndex % 2 === 0) ? 1 : 2;
                            const targetInput = nextCard.querySelector(
                                `input[data-player="${targetPlayerSlot}"]`);
                            const targetNameSpan = targetInput.previousElementSibling;

                            targetNameSpan.textContent = data.winner_name;
                            targetNameSpan.style.color = '#00ff7f';
                            setTimeout(() => {
                                targetNameSpan.style.color = '';
                            }, 1000);

                            // Logic mở khóa đơn giản
                            targetInput.disabled = false;
                        }
                    }

                    // === 3. XỬ LÝ NGƯỜI THUA (VÀO TRANH HẠNG 3) ===
                    // Kiểm tra xem server có trả về tên người thua không
                    if (data.loser_name && data.loser_info) {
                        const lRound = data.loser_info.round_number;
                        const lIndex = data.loser_info.match_index;
                        const lSlot = data.loser_info.slot; // 1 hoặc 2

                        // QUAN TRỌNG: Thêm [data-group="loser"] để tìm chính xác ở NHÁNH THUA
                        // (Tránh nhầm sang nhánh thắng có cùng số round/index)
                        const loserCard = document.querySelector(
                            `.match-card[data-group="loser"][data-round="${lRound}"][data-index="${lIndex}"]`
                        );

                        if (loserCard) {
                            const targetInput = loserCard.querySelector(`input[data-player="${lSlot}"]`);
                            const opponentSlot = (lSlot === 1) ? 2 : 1;
                            const opponentInput = loserCard.querySelector(
                                `input[data-player="${opponentSlot}"]`);

                            if (targetInput) {
                                // Điền tên người thua
                                const nameSpan = targetInput.previousElementSibling;
                                nameSpan.textContent = data.loser_name;

                                // Hiệu ứng nháy màu vàng
                                nameSpan.style.color = '#ffc107';
                                nameSpan.style.fontWeight = 'bold';
                                setTimeout(() => {
                                    nameSpan.style.color = '';
                                    nameSpan.style.fontWeight = '';
                                }, 2000);

                                // Mở khóa nếu đối thủ đã có mặt
                                const opponentName = opponentInput.previousElementSibling.textContent
                                    .trim();
                                if (opponentName !== '---' && opponentName !== '') {
                                    targetInput.disabled = false;
                                    opponentInput.disabled = false;
                                } else {
                                    targetInput.disabled = true;
                                }
                            }
                        }
                    }

                    // Ưu tiên 2: Logic cũ (Dành cho Tranh hạng 3 - Single Elimination)
                    // Chỉ chạy khi KHÔNG CÓ loser_info (tức là giải loại trực tiếp)
                    else if (data.loser_name && !data.loser_info) {
                        const nextRound = currentRound + 1;
                        const thirdPlaceCard = document.querySelector(
                            `.match-card[data-round="${nextRound}"][data-index="1"]`);

                        if (thirdPlaceCard) {
                            const lSlot = (currentIndex % 2 === 0) ? 1 : 2;
                            const lOpponentSlot = (lSlot === 1) ? 2 : 1;

                            const loserInput = thirdPlaceCard.querySelector(
                                `input[data-player="${lSlot}"]`);
                            const opponentInput = thirdPlaceCard.querySelector(
                                `input[data-player="${lOpponentSlot}"]`);

                            if (loserInput) {
                                const loserNameSpan = loserInput.previousElementSibling;
                                loserNameSpan.textContent = data.loser_name;
                                loserNameSpan.style.color = '#ffc107';
                                setTimeout(() => {
                                    loserNameSpan.style.color = '';
                                }, 1000);

                                const opponentName = opponentInput.previousElementSibling.textContent
                                    .trim();
                                if (opponentName !== '---' && opponentName !== '') {
                                    loserInput.disabled = false;
                                    opponentInput.disabled = false;
                                }
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
            // Tìm tất cả các container chứa sơ đồ
            const containers = document.querySelectorAll('.bracket-container');

            containers.forEach(container => {
                // SỬA LỖI: Tìm thẻ svg bất kể là id hay class
                const svg = container.querySelector('svg');

                if (!svg) return;

                // Reset SVG
                svg.innerHTML = '';
                svg.setAttribute('width', container.scrollWidth);
                svg.setAttribute('height', container.scrollHeight);

                const matches = container.querySelectorAll('.match-card');

                matches.forEach(match => {
                    const round = parseInt(match.dataset.round);
                    const index = parseInt(match.dataset.index);
                    // Nếu không có bracketType (code cũ), mặc định là 'single'
                    const type = match.dataset.group || 'single';

                    let nextRound = round + 1;
                    let nextIndex = 0;

                    // === LOGIC TÌM TRẬN TIẾP THEO ===
                    if (type === 'loser') {
                        // Nhánh thua: Vòng lẻ đi thẳng, Vòng chẵn nhập đôi
                        if (round % 2 !== 0) {
                            nextIndex = index;
                        } else {
                            nextIndex = Math.floor(index / 2);
                        }
                    } else {
                        // Nhánh thắng & Loại trực tiếp: Luôn nhập đôi
                        nextIndex = Math.floor(index / 2);
                    }

                    // Tìm thẻ HTML của trận tiếp theo
                    const nextMatch = container.querySelector(
                        `.match-card[data-round="${nextRound}"][data-index="${nextIndex}"]`
                    );

                    if (nextMatch) {
                        drawPath(container, svg, match, nextMatch);
                    }
                    // Vẽ dây nối tới Chung kết tổng (nếu đang ở nhánh thắng/thua)
                    // else if (type === 'winner' || type === 'loser') {
                    //     const finalMatch = document.querySelector(
                    //         '.match-card[data-group="final"]');
                    //     if (finalMatch) drawPath(container, svg, match, finalMatch, true);
                    // }
                });
            });
        }

        // Hàm vẽ đường nối
        function drawPath(container, svg, startEl, endEl, isCrossContainer = false) {
            const startRect = startEl.getBoundingClientRect();
            const endRect = endEl.getBoundingClientRect();
            const containerRect = container.getBoundingClientRect();

            // Nếu nối sang container khác (Chung kết), cần tính toán lại tọa độ gốc
            // Ở đây ta dùng toạ độ tương đối với container hiện tại

            const scrollLeft = container.scrollLeft;
            const scrollTop = container.scrollTop;

            const x1 = (startRect.right - containerRect.left) + scrollLeft;
            const y1 = (startRect.top + startRect.height / 2 - containerRect.top) + scrollTop;

            // Nếu endEl nằm ngoài (như chung kết), ta tính tương đối dựa trên vị trí màn hình
            let x2, y2;

            if (isCrossContainer) {
                // Tính khoảng cách tương đối giữa 2 container
                const offsetX = endRect.left - startRect.right;
                const offsetY = endRect.top - startRect.top;

                x2 = x1 + offsetX + (endRect.width / 2); // Nối vào giữa hoặc cạnh trái
                y2 = y1 + offsetY;

                // Nếu chung kết nằm ngay bên phải
                x2 = (endRect.left - containerRect.left) + scrollLeft;
                y2 = (endRect.top + endRect.height / 2 - containerRect.top) + scrollTop;
            } else {
                x2 = (endRect.left - containerRect.left) + scrollLeft;
                y2 = (endRect.top + endRect.height / 2 - containerRect.top) + scrollTop;
            }

            const xMid = x1 + (x2 - x1) / 2;
            const pathStr = `M ${x1} ${y1} L ${xMid} ${y1} L ${xMid} ${y2} L ${x2} ${y2}`;

            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", pathStr);
            path.setAttribute("stroke", "#555");
            path.setAttribute("stroke-width", "2");
            path.setAttribute("fill", "none");

            svg.appendChild(path);
        }

        setTimeout(drawBracketLines, 200);
        window.addEventListener('resize', drawBracketLines);

        const bracketTabBtn = document.getElementById('bracket-tab');
        if (bracketTabBtn) {
            bracketTabBtn.addEventListener('shown.bs.tab', function() {
                setTimeout(drawBracketLines, 50);
            });
        }
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
