<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $tournaments = Tournament::orderBy('created_at', 'desc')->take(4)->get();
        return view('home.index', compact('tournaments'));
    }

    public function list(Request $request)
    {
        $query = Tournament::query();

        // 1. Tìm kiếm theo tên (Search)
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 2. Lọc theo Thể loại (Category)
        if ($request->has('category') && $request->category != '') {
            $sportGames = ["Bóng đá", "Bóng rổ", "Cầu lông", "Bóng chuyền", "Bơi lội", "Chạy bộ"];
            $eSportGames = ["Liên Minh Huyền Thoại", "Valorant", "CS2", "PUBG Mobile", "Tốc Chiến", "Dota 2"];

            if ($request->category == 'sport') {
                $query->whereIn('game_name', $sportGames);
            } elseif ($request->category == 'e-sport') {
                $query->whereIn('game_name', $eSportGames);
            }
        }

        // 3. Lọc theo Bộ môn (Game Name)
        if ($request->has('game_name') && $request->game_name != '') {
            $query->where('game_name', $request->game_name);
        }

        $tournaments = $query->orderBy('created_at', 'desc')->paginate(16)->withQueryString();

        return view('home.tournaments.index', compact('tournaments'));
    }
}
