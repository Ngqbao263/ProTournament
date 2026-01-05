<?php

namespace App\Http\Controllers\User;

use App\Models\Matches;

class DoubleElimination
{
    /**
     * TẠO SƠ ĐỒ THI ĐẤU (Nhánh thắng - Nhánh thua)
     */
    public function generateDoubleElimination($tournament, $players)
    {
        $playerCount = $players->count();
        $pow = ceil(log($playerCount, 2));
        $totalPositions = pow(2, $pow); // Ví dụ 8, 16, 32...

        // 1. TẠO NHÁNH THẮNG (WB) - Giống hệt loại trực tiếp
        // ---------------------------------------------------
        $bracketPlayers = $players->all();
        // Điền người ảo vào cho đủ slot
        for ($i = $playerCount; $i < $totalPositions; $i++) {
            $bracketPlayers[] = null;
        }
        shuffle($bracketPlayers);

        $wbRounds = $pow; // Tổng số vòng nhánh thắng

        // Tạo các trận nhánh thắng (WB)
        for ($round = 1; $round <= $wbRounds; $round++) {
            $matchesInRound = $totalPositions / pow(2, $round);
            for ($i = 0; $i < $matchesInRound; $i++) {
                $p1 = ($round == 1) ? ($bracketPlayers[$i * 2] ?? null) : null;
                $p2 = ($round == 1) ? ($bracketPlayers[$i * 2 + 1] ?? null) : null;

                Matches::create([
                    'tournament_id' => $tournament->id,
                    'round_number' => $round,
                    'match_index' => $i,
                    'player1_id' => $p1 ? $p1->id : null,
                    'player2_id' => $p2 ? $p2->id : null,
                    'group' => 'winner' // Đánh dấu là nhánh thắng
                ]);
            }
        }

        // 2. TẠO NHÁNH THUA (LB) - Tạo khung sẵn
        // ---------------------------------------------------
        // Số vòng nhánh thua thường = (Số vòng nhánh thắng - 1) * 2
        // Logic tạo nhánh thua khá phức tạp, ở đây ta tạo các slot trận đấu trước

        $lbRounds = ($wbRounds - 1) * 2;
        $lbMatchesCount = $totalPositions / 4; // Bắt đầu với nửa số trận của WB Round 1

        for ($r = 1; $r <= $lbRounds; $r++) {
            for ($i = 0; $i < $lbMatchesCount; $i++) {
                Matches::create([
                    'tournament_id' => $tournament->id,
                    'round_number' => $r,
                    'match_index' => $i,
                    'player1_id' => null,
                    'player2_id' => null,
                    'group' => 'loser' // Đánh dấu là nhánh thua
                ]);
            }
            // Số trận giảm đi một nửa sau mỗi 2 vòng (Ví dụ: 4 -> 4 -> 2 -> 2 -> 1 -> 1)
            if ($r % 2 == 0) {
                $lbMatchesCount /= 2;
            }
        }

        // 3. TẠO CHUNG KẾT TỔNG (Grand Final)
        // ---------------------------------------------------
        Matches::create([
            'tournament_id' => $tournament->id,
            'round_number' => $wbRounds + 1, // Vòng sau cùng của nhánh thắng
            'match_index' => 0,
            'player1_id' => null, // Vô địch nhánh thắng
            'player2_id' => null, // Vô địch nhánh thua
            'group' => 'final'
        ]);

        // Tự động đẩy người thắng ở vòng 1 nếu gặp người ảo
        $this->advanceByes($tournament->id);
    }

    /**
     * XỬ LÝ KẾT QUẢ TRẬN ĐẤU (Logic rớt nhánh)
     */
    public function processWin($match, $winnerId, $score1, $score2)
    {
        // Cập nhật trận hiện tại
        $match->update([
            'score1' => $score1,
            'score2' => $score2,
            'winner_id' => $winnerId
        ]);

        $loserId = ($winnerId == $match->player1_id) ? $match->player2_id : $match->player1_id;

        // ==========================================
        // TRƯỜNG HỢP 1: TRẬN ĐẤU THUỘC NHÁNH THẮNG (WINNER BRACKET)
        // ==========================================
        if ($match->group == 'winner') {

            // 1. Người THẮNG -> Tiến lên vòng sau nhánh thắng (hoặc vào Chung kết tổng)
            // -------------------------------------------------------------------------
            $nextRound = $match->round_number + 1;
            $nextIndex = floor($match->match_index / 2);

            // Kiểm tra xem vòng tiếp theo là nhánh thắng hay là Chung kết tổng
            $nextWBMatch = Matches::where('tournament_id', $match->tournament_id)
                ->where('round_number', $nextRound)
                ->where('match_index', $nextIndex)
                ->where('group', 'winner')
                ->first();

            if ($nextWBMatch) {
                // Vẫn còn trong nhánh thắng
                if ($match->match_index % 2 == 0) $nextWBMatch->update(['player1_id' => $winnerId]);
                else $nextWBMatch->update(['player2_id' => $winnerId]);
            } else {
                // Hết nhánh thắng -> Vào slot 1 của Chung Kết Tổng
                $finalMatch = Matches::where('tournament_id', $match->tournament_id)
                    ->where('group', 'final')->first();
                if ($finalMatch) $finalMatch->update(['player1_id' => $winnerId]);
            }

            // 2. Người THUA -> Rớt xuống nhánh thua (LOSER BRACKET)
            // -------------------------------------------------------------------------
            if ($loserId) {
                $this->dropToLoserBracket($match, $loserId);
            }
        }

        // ==========================================
        // TRƯỜNG HỢP 2: TRẬN ĐẤU THUỘC NHÁNH THUA (LOSER BRACKET)
        // ==========================================
        elseif ($match->group == 'loser') {

            // Người THUA -> Bị loại luôn (Không làm gì cả)

            // Người THẮNG -> Tiến lên vòng sau nhánh thua
            $nextRound = $match->round_number + 1;

            // Logic nhánh thua:
            // - Vòng lẻ lên vòng chẵn: Giữ nguyên index (ghép với người rớt từ trên xuống)
            // - Vòng chẵn lên vòng lẻ: Index chia đôi (tự đấu với nhau)

            if ($match->round_number % 2 != 0) {
                // Vòng lẻ -> Vòng chẵn: Index giữ nguyên
                $nextIndex = $match->match_index;
                $slot = 2; // Slot 1 dành cho người rớt từ WB xuống
            } else {
                // Vòng chẵn -> Vòng lẻ: Index chia đôi
                $nextIndex = floor($match->match_index / 2);
                $slot = ($match->match_index % 2 == 0) ? 1 : 2;
            }

            $nextLBMatch = Matches::where('tournament_id', $match->tournament_id)
                ->where('round_number', $nextRound)
                ->where('match_index', $nextIndex)
                ->where('group', 'loser')
                ->first();

            if ($nextLBMatch) {
                if ($slot == 1) $nextLBMatch->update(['player1_id' => $winnerId]);
                else $nextLBMatch->update(['player2_id' => $winnerId]);
            } else {
                // Nếu không còn trận nhánh thua nào -> Vào slot 2 của Chung Kết Tổng
                $finalMatch = Matches::where('tournament_id', $match->tournament_id)
                    ->where('group', 'final')->first();
                if ($finalMatch) $finalMatch->update(['player2_id' => $winnerId]);
            }
        }

        // ==========================================
        // TRƯỜNG HỢP 3: CHUNG KẾT TỔNG
        // ==========================================
        elseif ($match->group == 'final') {
             // Kết thúc giải, controller updateMatch đã có logic set status = finished
        }
    }

    /**
     * Logic đưa người thua từ WB xuống LB
     */
    protected function dropToLoserBracket($wbMatch, $loserId)
    {
        // Công thức tính toán vị trí rớt xuống nhánh thua
        // WB Round 1 -> LB Round 1
        // WB Round 2 -> LB Round 3
        // WB Round 3 -> LB Round 5

        $roundMapping = ($wbMatch->round_number - 1) * 2 + 1;

        // Match Index mapping (Phức tạp hơn để tránh gặp lại đối thủ cũ sớm)
        // Cơ bản: WB R1 -> LB R1: Index chia đôi
        $targetIndex = floor($wbMatch->match_index / 2);

        if ($wbMatch->round_number > 1) {
            // Các vòng sau: Rớt vào vòng lẻ của LB
            $roundMapping = ($wbMatch->round_number - 1) * 2;
            // Logic đảo ngược vị trí để cân bằng nhánh (cơ bản)
            $targetIndex = $wbMatch->match_index;
        }

        $lbMatch = Matches::where('tournament_id', $wbMatch->tournament_id)
            ->where('group', 'loser')
            ->where('round_number', $roundMapping)
            ->where('match_index', $targetIndex)
            ->first();

        if ($lbMatch) {
            // Luật điền: Thường điền vào slot trống đầu tiên (thường là slot 1 cho người rớt xuống)
            if (!$lbMatch->player1_id) $lbMatch->update(['player1_id' => $loserId]);
            else $lbMatch->update(['player2_id' => $loserId]);
        }
    }

    protected function advanceByes($tournamentId)
    {
        // Tự động cho thắng vòng 1 nhánh thắng nếu gặp người ảo
        $round1Matches = Matches::where('tournament_id', $tournamentId)
            ->where('group', 'winner')
            ->where('round_number', 1)
            ->get();

        foreach ($round1Matches as $match) {
            if ($match->player1_id && !$match->player2_id) {
                $this->processWin($match, $match->player1_id, null, null);
            } elseif (!$match->player1_id && $match->player2_id) {
                $this->processWin($match, $match->player2_id, null, null);
            }
        }
    }
}