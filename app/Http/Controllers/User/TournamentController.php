<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\Matches;
use Illuminate\Support\Facades\Auth;

class TournamentController extends Controller
{
    //Phần tạo giải đấu
    public function create()
    {
        return view('home.tournaments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category' => 'required|string',
            'game_name' => 'required|string',
            'start_date' => 'required',
            'description' => 'nullable|string',
            'type' => 'required',
            'max_player' => 'required|integer|min:2',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'name.required' => 'Vui lòng nhập tên giải đấu.',
            'category.required' => 'Vui lòng chọn thể loại thi đấu.',
            'game_name.required' => 'Vui lòng chọn bộ môn thi đấu.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu giải.',
            'type.required' => 'Vui lòng chọn thể thức thi đấu.',
            'max_player.required' => 'Vui lòng nhập số lượng người tham gia tối đa.',
            'thumbnail.max' => 'Ảnh không được lớn hơn 2MB.',
        ]);

        $tournament = Tournament::create([
            'name' => $request->name,
            'category' => $request->category,
            'game_name' => $request->game_name,
            'start_date' => $request->start_date,
            'description' => $request->description,
            'type' => $request->type,
            'max_player' => $request->max_player,
            'status' => 'open',
            'creator_id' => Auth::id(),
        ]);

            //Nếu có upload ảnh thì lưu vào storage/public/thumbnail_tournament
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnail_tournament', 'public');
            $tournament->update(['thumbnail' => $path]);
        }
        else {
            $tournament->update(['thumbnail' => 'home/img/default.png']);
        }

        return redirect()->route('tournament.show', $tournament->id)->with('success', 'Tạo giải đấu thành công!');
    }

    //Phần chi tiết giải đấu
    public function show($id)
    {
        $tournament = Tournament::with([
            'players',
            'matches.player1',
            'matches.player2'
        ])->findOrFail($id);

        // Gom nhóm matches theo vòng đấu
        $rounds = $tournament->matches->sortBy('match_index')->groupBy('round_number');

        // Tạo response và thêm header để TẮT CACHE
        $response = response(
            view('home.tournaments.show', compact('tournament', 'rounds'))
        );

        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');

        return $response;
    }

    public function approvePlayer($id)
    {
        $player = Player::findOrFail($id);
        $player->update(['status' => 'approved']);
        return back()->with('success', 'Đã duyệt người chơi');
    }

    // Trang giải đấu của tôi
    public function myTournaments()
    {
        $userId = Auth::id();
        $userName = Auth::user()->name;

        // 1. Lấy giải đấu do mình TẠO (Mới nhất lên đầu)
        $createdTournaments = Tournament::where('creator_id', $userId)
                                        ->orderBy('created_at', 'desc')
                                        ->get();

        // 2. Lấy giải đấu mình ĐÃ THAM GIA
        // (Kiểm tra theo user_id hoặc theo tên cho chắc chắn)
        $joinedTournaments = Tournament::whereHas('players', function($q) use ($userId, $userName) {
            $q->where('user_id', $userId)
              ->orWhere('name', $userName);
        })->orderBy('created_at', 'desc')->get();

        return view('home.mytournament', compact('createdTournaments', 'joinedTournaments'));
    }

    //Thêm người chơi
    //Người chơi xin vào
    public function join($id)
    {
        $tournament = Tournament::findOrFail($id);

        // Kiểm tra nếu đủ người chơi
        if ($tournament->players_count >= $tournament->max_player) {
            return response()->json(['status' => 'error', 'message' => 'Giải đấu đã đủ người chơi!']);
        }

        // Kiểm tra nếu đã xin vào rồi
        $exists = Player::where('tournament_id', $id)
                        ->where('name', Auth::user()->name)
                        ->exists();
        if ($exists) {
            return response()->json(['status' => 'warning', 'message' => 'Bạn đã đăng ký tham gia rồi, vui lòng chờ duyệt!']);
        }

        Player::create([
            'tournament_id' => $id,
            'name' => Auth::user()->name,
            'status' => 'pending',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Đăng ký thành công, vui lòng chờ duyệt!']);
    }

    //Người tạo giải tự thêm
    public function addPlayer(Request $request, $id)
    {
        $tournament = Tournament::withCount(['players' => function($q){ $q->where('status','approved'); }])->findOrFail($id);

        if ($tournament->creator_id != Auth::id()) {
            return response()->json(['error' => 'Không có quyền'], 403);
        }

        if ($tournament->players_count >= $tournament->max_player) {
            return response()->json(['error' => 'Giải đấu đã đủ người chơi!'], 400);
        }

        $request->validate(['name' => 'required|string|max:255']);

        $player = Player::create([
            'tournament_id' => $id,
            'name' => $request->name,
            'status' => 'approved',
        ]);

        // trả JSON cho frontend
        return response()->json(['id' => $player->id, 'name' => $player->name]);
    }


    // Sửa tên người chơi
    public function updatePlayer(Request $request, $id)
    {
        $player = Player::findOrFail($id);
        $tournament = $player->tournament;

        if ($tournament->creator_id != Auth::id()) {
            return response()->json(['error' => 'Không có quyền'], 403);
        }

        $request->validate(['name' => 'required|string|max:255']);
        $player->update(['name' => $request->name]);

        // nếu AJAX => trả JSON, ngược lại redirect (dự phòng)
        if ($request->ajax()) {
            return response()->json(['success' => true, 'name' => $player->name]);
        }

        return back()->with('success', 'Cập nhật tên người chơi thành công!');
    }


    public function destroy(Request $request, $id)
    {
        $player = Player::findOrFail($id);
        $tournament = $player->tournament;

        if ($tournament->creator_id != Auth::id()) {
            return response()->json(['error' => 'Không có quyền'], 403);
        }

        $player->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Đã xóa người chơi!');
    }

    // Phần bảng đấu
    public function getplayers($id)
    {
        $tournament = Tournament::with('players')->findOrFail($id);
        $players = $tournament->players()->where('status', 'approved')->pluck('name')->toArray();

        return view('home.tournaments.show', compact('tournament', 'players'));
    }


    //Phần lưu kết quả trận đấu
    public function updateMatch(Request $request, Matches $match)
    {
        // Lấy thông tin giải đấu của trận này
        $tournament = Tournament::findOrFail($match->tournament_id);

        if ($tournament->creator_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa tỉ số!'
            ], 403);
        }

        $score1 = $request->score1;
        $score2 = $request->score2;
        $winnerId = null;

        // Tự động tính winner
        if (!is_null($score1) && !is_null($score2)) {
            if ((int)$score1 > (int)$score2) {
                $winnerId = $match->player1_id;
            } elseif ((int)$score2 > (int)$score1) {
                $winnerId = $match->player2_id;
            }
        }

        $this->processWin($match, $winnerId, $score1, $score2);

        // Lấy lại thông tin match mới nhất để biết tên winner
        $match->refresh();

        return response()->json([
            'success' => true,
            'winner_name' => $match->winner ? $match->winner->name : null, // Trả về tên người thắng
            'winner_id' => $winnerId
        ]);
    }

    public function startTournament($id)
    {
        $tournament = Tournament::findOrFail($id);
        $players = $tournament->players()->where('status', 'approved')->get();
        $playerCount = $players->count();

        if ($playerCount < 2) {
            return back()->with('error', 'Cần ít nhất 2 người chơi!');
        }

        // 1. Tính toán số lượng cần thiết (lũy thừa của 2: 2, 4, 8, 16, 32...)
        // Ví dụ: 6 người chơi => cần sơ đồ 8 (sẽ có 2 slot trống - BYE)
        $pow = ceil(log($playerCount, 2));
        $totalPositions = pow(2, $pow);
        $totalRounds = $pow;

        // Tạo danh sách người chơi đầy đủ (thêm các vị trí NULL nếu thiếu)
        $bracketPlayers = $players->all();
        for ($i = $playerCount; $i < $totalPositions; $i++) {
            $bracketPlayers[] = null; // người chơi ảo
        }

        // Random vị trí thi đấu
        shuffle($bracketPlayers);

        // 2. Tạo các trận đấu cho tất cả các vòng
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

        // Tạo trận tranh hạng 3 (nếu có 4+ người)
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

        $tournament->update(['status' => 'started']);

        // 3. Tự động xử lý các trận có người chơi ảo (miễn đấu) ở vòng 1
        $this->advanceByes($tournament->id);

        return back()->with('success', 'Giải đấu đã bắt đầu! Sơ đồ thi đấu đã được tạo.');
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
                // SỬA Ở ĐÂY: Gửi (null, null) thay vì (1, 0)
                $this->processWin($match, $match->player1_id, null, null);
            }
            // Nếu không có player1 mà có player2
            elseif (!$match->player1_id && $match->player2_id) {
                // SỬA Ở ĐÂY: Gửi (null, null) thay vì (0, 1)
                $this->processWin($match, $match->player2_id, null, null);
            }
        }
    }

    // Hàm phụ: Xử lý thắng thua và cập nhật vòng sau
    private function processWin($match, $winnerId, $score1, $score2)
    {
        // Cập nhật điểm và người thắng
        $match->score1 = $score1;
        $match->score2 = $score2;
        $match->winner_id = $winnerId;
        $match->save();

        // Tìm trận tiếp theo để điền tên người thắng vào
        // (Giữ nguyên logic tìm $nextMatch)
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

    // Lịch thi đấu
    public function updateMatchTime(Request $request, Matches $match)
    {
        $tournament = Tournament::findOrFail($match->tournament_id);

        if ($tournament->creator_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền!'], 403);
        }

        $request->validate([
            'match_time' => 'required|date'
        ]);

        $match->update(['match_time' => $request->match_time]);

        return response()->json([
            'success' => true,
            'formatted_time' => $match->match_time->format('H:i d/m/Y')
        ]);
    }

    public function test()
    {
        return view('test');
    }
}
