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

        // Lọc theo thể loại
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Lọc theo bộ môn
        if ($request->filled('game_name')) {
            $query->where('game_name', $request->game_name);
        }

        // Tìm kiếm theo tên
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sắp xếp mới nhất + phân trang
        $tournaments = $query->orderBy('created_at', 'desc')->paginate(16);

        // Giữ lại giá trị khi lọc
        return view('home.tournaments.index', [
            'tournaments' => $tournaments,
            'filters' => $request->only(['category', 'game_name', 'search'])
        ]);
    }

}
