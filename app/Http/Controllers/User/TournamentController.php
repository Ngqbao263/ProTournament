<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\SingleElimination;
use App\Http\Controllers\User\DoubleElimination;
use App\Models\Player;
use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\Matches;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'start_date' => 'required|date|after_or_equal:today',
            'description' => 'nullable|string',
            'type' => 'required',
            'mode' => 'required|in:individual,team',
            'max_player' => 'required|integer|min:4',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'name.required' => 'Vui lòng nhập tên giải đấu.',
            'category.required' => 'Vui lòng chọn thể loại thi đấu.',
            'game_name.required' => 'Vui lòng chọn bộ môn thi đấu.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu giải.',
            'start_date.after_or_equal' => 'Ngày bắt đầu không được là ngày trong quá khứ.',
            'type.required' => 'Vui lòng chọn thể thức thi đấu.',
            'max_player.required' => 'Vui lòng nhập số lượng người tham gia tối đa.',
            'max_player.min' => 'Sô lượng người chơi phải lớn hơn 4',
            'thumbnail.max' => 'Ảnh không được lớn hơn 2MB.',
            'thumbnail.mimes' => 'Chỉ chấp nhận các file có đuôi jpg,jpeg,png,gif,webp.',
        ]);

        $tournament = Tournament::create([
            'name' => $request->name,
            'category' => $request->category,
            'game_name' => $request->game_name,
            'start_date' => $request->start_date,
            'description' => $request->description,
            'type' => $request->type,
            'mode' => $request->mode,
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

    public function edit($id)
    {
        $tournament = Tournament::findOrFail($id);

        // Kiểm tra quyền: Chỉ người tạo hoặc status là 'open' mới được sửa (tùy logic của bạn)
        if ($tournament->creator_id != Auth::id()) {
            return redirect()->route('home')->with('error', 'Bạn không có quyền chỉnh sửa giải đấu này.');
        }

        // Nếu giải đang diễn ra hoặc kết thúc, có thể chặn sửa một số thông tin quan trọng
        // if ($tournament->status != 'open') { ... }

        return view('home.tournaments.edit', compact('tournament'));
    }

    // 2. Hàm xử lý cập nhật
    public function update(Request $request, $id)
    {
        $tournament = Tournament::findOrFail($id);

        if ($tournament->creator_id != Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|max:255',
            'category' => 'required|string',
            'game_name' => 'required|string',
            'start_date' => 'required|date', // Bỏ after_or_equal:today để tránh lỗi nếu không sửa ngày
            'description' => 'nullable|string',
            'type' => 'required',
            'mode' => 'required|in:individual,team',
            'max_player' => 'required|integer|min:2',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'category' => $request->category,
            'game_name' => $request->game_name,
            'start_date' => $request->start_date,
            'description' => $request->description,
            'type' => $request->type,
            'mode' => $request->mode,
            'max_player' => $request->max_player,
        ];

        // Xử lý ảnh thumbnail
        if ($request->hasFile('thumbnail')) {
            // Xóa ảnh cũ nếu không phải ảnh mặc định (tùy logic)
            // if ($tournament->thumbnail && $tournament->thumbnail != 'home/img/default.png') {
            //     Storage::disk('public')->delete($tournament->thumbnail);
            // }

            $path = $request->file('thumbnail')->store('thumbnail_tournament', 'public');
            $data['thumbnail'] = $path;
        }

        $tournament->update($data);

        return redirect()->route('tournament.show', $tournament->id)->with('success', 'Cập nhật giải đấu thành công!');
    }

    //Phần chi tiết giải đấu
    public function show($id)
    {
        // Eager load các quan hệ cần thiết
        $tournament = Tournament::with([
            'players',
            'matches.player1',
            'matches.player2',
            'matches.winner' // Load thêm winner để tiện truy xuất tên
        ])->findOrFail($id);

        // Gom nhóm matches theo vòng đấu để hiển thị ở tab Bracket (cho Single Elim)
        $rounds = $tournament->matches->sortBy('match_index')->groupBy('round_number');

        // 1. TÍNH TOÁN BẢNG XẾP HẠNG (Thống kê thắng/thua/hiệu số)
        $rankings = $tournament->players->where('status', 'approved')->map(function($player) use ($tournament) {
            // Đếm số trận thắng
            $wins = $tournament->matches->where('winner_id', $player->id)->count();

            // Tính hiệu số (Tổng điểm thắng - Tổng điểm thua)
            $scoreDiff = 0;
            foreach($tournament->matches as $match) {
                if ($match->player1_id == $player->id && !is_null($match->score1)) {
                    $scoreDiff += ($match->score1 - $match->score2);
                }
                elseif ($match->player2_id == $player->id && !is_null($match->score2)) {
                    $scoreDiff += ($match->score2 - $match->score1);
                }
            }

            return [
                'player' => $player,
                'wins' => $wins,
                'score_diff' => $scoreDiff,
                'rank_label' => 'Vòng loại', // Mặc định
                'medal' => null,
                'sort_order' => 100 // Mặc định xếp cuối
            ];
        });

        // 2. XÁC ĐỊNH DANH HIỆU (TOP 3) DỰA TRÊN THỂ THỨC
        $championId = null;
        $runnerUpId = null;
        $thirdPlaceId = null;

        // --- TRƯỜNG HỢP A: LOẠI TRỰC TIẾP (Single Elimination) ---
        if ($tournament->type == 'single_elimination') {
            $finalRound = $rounds->last(); // Vòng cuối cùng
            if ($finalRound) {
                // Trận Chung kết là index 0
                $finalMatch = $finalRound->firstWhere('match_index', 0);
                // Trận Tranh hạng 3 là index 1
                $thirdMatch = $finalRound->firstWhere('match_index', 1);

                if ($finalMatch && $finalMatch->winner_id) {
                    $championId = $finalMatch->winner_id;
                    $runnerUpId = ($finalMatch->winner_id == $finalMatch->player1_id)
                                   ? $finalMatch->player2_id
                                   : $finalMatch->player1_id;
                }
                if ($thirdMatch && $thirdMatch->winner_id) {
                    $thirdPlaceId = $thirdMatch->winner_id;
                }
            }
        }
        // --- TRƯỜNG HỢP B: NHÁNH THẮNG - NHÁNH THUA (Double Elimination) ---
        elseif ($tournament->type == 'double_elimination') {
            // Tìm trận Chung kết tổng (có group='final')
            $grandFinal = $tournament->matches->firstWhere('group', 'final');

            if ($grandFinal && $grandFinal->winner_id) {
                $championId = $grandFinal->winner_id;
                $runnerUpId = ($grandFinal->winner_id == $grandFinal->player1_id)
                               ? $grandFinal->player2_id
                               : $grandFinal->player1_id;
            }

            // Tìm Hạng 3: Là người THUA ở trận chung kết NHÁNH THUA
            // (Lấy trận thuộc group 'loser' có round_number lớn nhất)
            $lastLoserMatch = $tournament->matches
                ->where('group', 'loser')
                ->sortByDesc('round_number')
                ->first();

            if ($lastLoserMatch && $lastLoserMatch->winner_id) {
                // Người thắng trận này vào CK Tổng -> Người thua trận này là Hạng 3
                $thirdPlaceId = ($lastLoserMatch->winner_id == $lastLoserMatch->player1_id)
                                ? $lastLoserMatch->player2_id
                                : $lastLoserMatch->player1_id;
            }
        }

        // 3. GÁN DANH HIỆU VÀO LIST RANKINGS
        $rankings = $rankings->map(function($item) use ($championId, $runnerUpId, $thirdPlaceId) {
            $pId = $item['player']->id;

            if ($championId && $pId == $championId) {
                $item['rank_label'] = '<span class="fw-bold text-warning">VÔ ĐỊCH</span>';
                $item['medal'] = '🥇';
                $item['sort_order'] = 1;
            }
            elseif ($runnerUpId && $pId == $runnerUpId) {
                $item['rank_label'] = '<span class="text-secondary fw-bold">Á Quân</span>';
                $item['medal'] = '🥈';
                $item['sort_order'] = 2;
            }
            elseif ($thirdPlaceId && $pId == $thirdPlaceId) {
                $item['rank_label'] = '<span class="fw-bold" style="color: #cd7f32">Hạng 3</span>';
                $item['medal'] = '🥉';
                $item['sort_order'] = 3;
            }

            return $item;
        });

        // 4. SẮP XẾP DANH SÁCH HOÀN CHỈNH
        // Ưu tiên: Danh hiệu (sort_order nhỏ nhất) -> Số trận thắng -> Hiệu số
        $rankings = $rankings->sortByDesc('score_diff')
                             ->sortByDesc('wins')
                             ->sortBy('sort_order')
                             ->values();

        // 5. TRẢ VỀ VIEW (Kèm header tắt cache để update real-time)
        $response = response(
            view('home.tournaments.show', compact('tournament', 'rounds', 'rankings'))
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
        return response()->json([
            'success' => true,
            'player' => $player,
            'members' => $player->members
        ]);
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
    public function join(Request $request, $id)
    {
        $tournament = Tournament::findOrFail($id);
        $userId = Auth::id();

        // 1. Kiểm tra số lượng
        if ($tournament->players_count >= $tournament->max_player) {
            return response()->json(['status' => 'error', 'message' => 'Giải đấu đã đủ số lượng!'], 400);
        }

        // 2. BẮT ĐẦU GIAO DỊCH
        DB::beginTransaction();

        try {
            // Check trùng (Dùng lock để tránh spam click)
            $exists = Player::where('tournament_id', $id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                DB::rollBack();
                return response()->json(['status' => 'warning', 'message' => 'Bạn đã đăng ký tham gia rồi!']);
            }

            // === TRƯỜNG HỢP A: ĐĂNG KÝ ĐỘI ===
            if ($request->has('team_name')) {
                // SỬ DỤNG VALIDATOR THỦ CÔNG (Để tránh bị Redirect khi lỗi)
                $validator = Validator::make($request->all(), [
                    'team_name' => 'required|string|max:255',
                    'members' => 'required|array|min:1',
                    'members.*' => 'required|string|distinct'
                ], [
                    'team_name.required' => 'Tên đội không được để trống',
                    'members.required' => 'Phải có ít nhất 1 thành viên',
                    'members.*.distinct' => 'Tên thành viên không được trùng nhau'
                ]);

                // Nếu Validate lỗi -> Trả về JSON ngay (Không Redirect)
                if ($validator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $validator->errors()->first() // Lấy lỗi đầu tiên
                    ], 422);
                }

                // Check trùng tên đội trong giải này
                $nameExists = Player::where('tournament_id', $id)->where('name', $request->team_name)->exists();
                if ($nameExists) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Tên đội này đã tồn tại trong giải!'], 422);
                }

                // Tạo Đội
                $team = Player::create([
                    'tournament_id' => $id,
                    'user_id' => $userId,
                    'name' => $request->team_name,
                    'status' => 'pending',
                ]);

                // Lưu thành viên
                foreach ($request->members as $memberName) {
                    if (!empty($memberName)) {
                        TeamMember::create([
                            'player_id' => $team->id,
                            'member_name' => $memberName
                        ]);
                    }
                }
                $message = 'Đăng ký Đội thành công! Vui lòng chờ duyệt.';
            }

            // === TRƯỜNG HỢP B: ĐĂNG KÝ CÁ NHÂN ===
            else {
                Player::create([
                    'tournament_id' => $id,
                    'user_id' => $userId,
                    'name' => Auth::user()->name,
                    'status' => 'pending',
                ]);
                $message = 'Đăng ký thành công, vui lòng chờ duyệt!';
            }

            DB::commit(); // Lưu thành công
            return response()->json(['status' => 'success', 'message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack(); // Hủy nếu lỗi hệ thống
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
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

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Ràng buộc: Tên phải unique trong bảng players, nhưng chỉ với tournament_id hiện tại
                Rule::unique('players')->where(function ($query) use ($id) {
                    return $query->where('tournament_id', $id);
                }),
            ]
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'name.unique' => 'Tên này đã tồn tại trong danh sách!',
        ]);

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

        // nếu AJAX => trả JSON, ngược lại redirect
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

    // Thêm thành viên vào đội
    public function storeTeam(Request $request, $playerId)
    {
        $request->validate(['member_name' => 'required']);

        TeamMember::create([
            'player_id' => $playerId,
            'member_name' => $request->member_name
        ]);

        return response()->json(['success' => true, 'message' => 'Thêm thành viên thành công']);
    }

    public function destroyTeam($id)
    {
        TeamMember::destroy($id);
        return response()->json(['success' => true]);
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

        if ($tournament->type == 'single_elimination') {
            $service = new SingleElimination();
            $service->processWin($match, $winnerId, $score1, $score2);
        }
        elseif ($tournament->type == 'double_elimination') {
            $logic = new DoubleElimination();
            $logic->processWin($match, $winnerId, $score1, $score2);
        }

        // Lấy lại thông tin match để trả về tên
        $match->refresh();
        $loserName = null;
        $loserInfo = null;
        $winnerInfo = null;

        if ($winnerId) {
            $loserId = ($winnerId == $match->player1_id) ? $match->player2_id : $match->player1_id;
            $loser = Player::find($loserId);
            $loserName = $loser ? $loser->name : null;
        }

        if ($loserId && $tournament->type == 'double_elimination') {
            // Tìm trận đấu ở nhánh thua (group='loser') mà người này vừa được thêm vào
            // Sắp xếp theo updated_at desc để lấy trận mới nhất vừa được update
            $nextLoserMatch = Matches::where('tournament_id', $tournament->id)
                ->where('group', 'loser')
                ->where(function($q) use ($loserId) {
                    $q->where('player1_id', $loserId)
                      ->orWhere('player2_id', $loserId);
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($nextLoserMatch) {
                $loserInfo = [
                    'round_number' => $nextLoserMatch->round_number,
                    'match_index'  => $nextLoserMatch->match_index,
                    'slot'         => ($nextLoserMatch->player1_id == $loserId) ? 1 : 2 // Biết đường điền vào ô trên hay ô dưới
                ];
            }
        }

        // 2. XỬ LÝ NGƯỜI THẮNG
        if ($winnerId) {
            // Tìm trận đấu TIẾP THEO mà người thắng vừa được điền tên vào
            // Logic: Tìm trận có chứa người thắng, vừa được cập nhật mới nhất (updated_at)
            $nextWinnerMatch = Matches::where('tournament_id', $tournament->id)
                ->where('id', '!=', $match->id) // Loại trừ chính trận vừa đấu
                ->where(function($q) use ($winnerId) {
                    $q->where('player1_id', $winnerId)
                      ->orWhere('player2_id', $winnerId);
                })
                // QUAN TRỌNG: Sắp xếp theo thời gian cập nhật giảm dần
                // Trận tiếp theo vừa được code processWin() update nên sẽ nằm đầu tiên
                ->orderBy('updated_at', 'desc')
                ->first();

                if ($nextWinnerMatch) {
                    // Lấy group từ DB
                    $nextGroup = $nextWinnerMatch->group;

                    // FIX LỖI: Nếu là giải Loại trực tiếp mà DB lưu là 'winners',
                    // ta đổi thành 'single' để khớp với HTML bên frontend
                    if ($tournament->type == 'single_elimination' && $nextGroup == 'winners') {
                        $nextGroup = 'single';
                    }

                    $winnerInfo = [
                        'group'        => $nextGroup, // <--- Dùng biến đã xử lý này
                        'round_number' => $nextWinnerMatch->round_number,
                        'match_index'  => $nextWinnerMatch->match_index,
                        'slot'         => ($nextWinnerMatch->player1_id == $winnerId) ? 1 : 2
                    ];
                }
        }

        // Kiểm tra giải kết thuc chưa
        if ($winnerId) {
            // Lấy vòng đấu lớn nhất (Chung kết)
            $maxRound = Matches::where('tournament_id', $tournament->id)->max('round_number');

            // Nếu trận đang nhập là Chung kết (Vòng lớn nhất) VÀ là trận index 0 (Chung kết tổng)
            if ($match->round_number == $maxRound && $match->match_index == 0) {
                $tournament->update(['status' => 'finished']);
            }
        }

        return response()->json([
            'success' => true,
            'winner_name' => $match->winner ? $match->winner->name : null, // Trả về tên người thắng
            'winner_id' => $winnerId,
            'loser_name' => $loserName, // Trả về tên người thua
            'loser_info' => $loserInfo,
            'winner_info' => $winnerInfo,
            'tournament_status' => $tournament->fresh()->status
        ]);
    }

    public function startTournament($id)
    {
        $tournament = Tournament::findOrFail($id);
        $players = $tournament->players()->where('status', 'approved')->get();


        if ($players->count() < 4) {
            return back()->with('error', 'Cần ít nhất 4 người chơi!');
        }

        // === ĐIỀU HƯỚNG DỰA TRÊN THỂ THỨC ===
        if ($tournament->type == 'single_elimination') {
            $logic = new SingleElimination();
            $logic->generateSingleElimination($tournament, $players);
        }
        elseif ($tournament->type == 'double_elimination') {
            $logic = new DoubleElimination();
            $logic->generateDoubleElimination($tournament, $players);
        }

        $tournament->update(['status' => 'started']);

        return back()->with('success', 'Giải đấu đã bắt đầu!');
    }





    // Hàm riêng để tạo giải Nhánh thắng - Nhánh thua
    private function generateDoubleElimination($tournament, $players)
    {

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
