<?php

namespace App\Http\Controllers\User;

use App\Models\Matches;

class SingleElimination
{
    // Tạo giải Loại trực tiếp
    public function generateSingleElimination($tournament, $players)
    {
        $playerCount = $players->count();
        // Tính toán số lượng cần thiết (lũy thừa của 2: 2, 4, 8, 16, 32...)
        // Ví dụ: 6 người chơi => cần sơ đồ 8 (tạo người chơi ảo cho chỗ trống)
        $pow = ceil(log($playerCount, 2));
        $totalPositions = pow(2, $pow);
        $totalRounds = $pow;

        // Tạo danh sách người chơi đầy đủ
        $bracketPlayers = $players->all();
        for ($i = $playerCount; $i < $totalPositions; $i++) {
            $bracketPlayers[] = null; // người chơi ảo
        }

        // Random vị trí thi đấu
        shuffle($bracketPlayers);

        // Tạo các trận đấu cho tất cả các vòng
        for ($round = 1; $round <= $totalRounds; $round++) {
            $matchesInRound = $totalPositions / pow(2, $round);

            for ($i = 0; $i < $matchesInRound; $i++) {
                // Chỉ vòng 1 mới có người chơi ngay từ đầu
                $p1 = ($round == 1) ? ($bracketPlayers[$i * 2] ?? null) : null;
                $p2 = ($round == 1) ? ($bracketPlayers[$i * 2 + 1] ?? null) : null;

                Matches::create([
                    'tournament_id' => $tournament->id,
                    'round_number' => $round,
                    'match_index' => $i,
                    'player1_id' => $p1 ? $p1->id : null,
                    'player2_id' => $p2 ? $p2->id : null,
                ]);
            }
        }

        // Tạo trận tranh hạng 3 (nếu có trên 4 người)
        // Trận này sẽ có round_number BẰNG vòng chung kết, nhưng match_index = 1
        if ($totalPositions >= 4) {
            Matches::create([
                'tournament_id' => $tournament->id,
                'round_number' => $totalRounds, // Cùng vòng với chung kết
                'match_index' => 1, // Chung kết là index 0, Hạng 3 là index 1
                'player1_id' => null, // Chờ người thua Bán kết 1
                'player2_id' => null, // Chờ người thua Bán kết 2
            ]);
        }

        // Tự động xử lý các trận có người chơi ảo (miễn đấu) ở vòng 1
        $this->advanceByes($tournament->id);
    }

    // Hàm phụ: Xử lý thắng thua và cập nhật vòng sau
    public function processWin($match, $winnerId, $score1, $score2)
    {
        // Cập nhật điểm và người thắng
        $match->score1 = $score1;
        $match->score2 = $score2;
        $match->winner_id = $winnerId;
        $match->save();

        // Tìm trận tiếp theo để điền tên người thắng vào
        $nextRound = $match->round_number + 1;
        $nextMatchIndex = floor($match->match_index / 2);

        $nextMatch = Matches::where('tournament_id', $match->tournament_id)
            ->where('round_number', $nextRound)
            ->where('match_index', $nextMatchIndex)
            ->first();

        if ($nextMatch) {
            // Nếu là trận chẵn (0, 2, 4...) ở vòng trước => vào slot Player 1 trận sau
            if ($match->match_index % 2 == 0) {
                $nextMatch->player1_id = $winnerId;
            } else {
            // Nếu là trận lẻ (1, 3, 5...) ở vòng trước => vào slot Player 2 trận sau
                $nextMatch->player2_id = $winnerId;
            }
            $nextMatch->save();
        }

        // Tìm trận tranh hạng 3 (index 1, cùng vòng $nextRound)
        $thirdPlaceMatch = Matches::where('tournament_id', $match->tournament_id)
            ->where('round_number', $nextRound)
            ->where('match_index', 1) // Trận hạng 3
            ->first();

        // Chỉ xử lý nếu đây là trận Bán kết (tức là có tồn tại trận hạng 3)
        if ($thirdPlaceMatch && $match->round_number == ($nextRound - 1)) {
            // Xác định người thua
            $loserId = null;
            if ($match->winner_id == $match->player1_id) {
                $loserId = $match->player2_id;
            } else if ($match->winner_id == $match->player2_id) {
                $loserId = $match->player1_id;
            }

            // Đưa người thua vào đúng slot
            if ($loserId) {
                if ($match->match_index == 0) { // Người thua ở Bán kết 1 (index 0)
                    $thirdPlaceMatch->player1_id = $loserId;
                } else if ($match->match_index == 1) { // Người thua ở Bán kết 2 (index 1)
                    $thirdPlaceMatch->player2_id = $loserId;
                }
                $thirdPlaceMatch->save();
            }
        }
    }

    // Hàm phụ: Tự động đẩy người thắng nếu gặp người chơi ảo
    private function advanceByes($tournamentId)
    {
        $round1Matches = Matches::where('tournament_id', $tournamentId)
                                ->where('round_number', 1)
                                ->get();

        foreach ($round1Matches as $match) {
            // Nếu có player1 mà không có player2 => player1 thắng tự động
            if ($match->player1_id && !$match->player2_id) {
                $this->processWin($match, $match->player1_id, null, null);
            }
            // Nếu không có player1 mà có player2
            elseif (!$match->player1_id && $match->player2_id) {
                $this->processWin($match, $match->player2_id, null, null);
            }
        }
    }
}


