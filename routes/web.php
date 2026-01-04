<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TournamentController as AdminTournamentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\TournamentController;

//Route cho phần Auth
Auth::routes(['verify' => true]);

//Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/list', [HomeController::class, 'list'])->name('list');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.users.index');
    })->name('dashboard');

    Route::resource('users', UserController::class);
});
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tournaments', function () {
        return redirect()->route('admin.tournaments.index');
    })->name('admin.tournaments.index');

    Route::resource('tournaments', AdminTournamentController::class);
});

//Route tạo giải đấu
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tournaments/create', [TournamentController::class, 'create'])->name('tournaments.create');
    Route::post('/tournaments', [TournamentController::class, 'store'])->name('tournaments.store');
    // Hiển thị form sửa
    Route::get('/tournaments/{id}/edit', [TournamentController::class, 'edit'])->name('tournaments.edit');
    Route::put('/tournaments/{id}', [TournamentController::class, 'update'])->name('tournaments.update');
    Route::post('/players/{id}/approve', [TournamentController::class, 'approvePlayer'])->name('player.approve');
    Route::post('/tournaments/{id}/join', [TournamentController::class, 'join'])->name('tournament.join');
    Route::post('/tournaments/{id}/add-player', [TournamentController::class, 'addPlayer'])->name('tournament.addPlayer');
    Route::put('/player/{id}', [TournamentController::class, 'updatePlayer'])->name('player.update');
    Route::delete('/player/{id}', [TournamentController::class, 'destroy'])->name('player.delete');

    //Kết quả trận đấu
    Route::put('/matches/{match}', [TournamentController::class, 'updateMatch'])->name('matches.update');
    Route::post('/tournaments/{id}/start', [TournamentController::class, 'startTournament'])->name('tournament.start');

    // Trang quản lý giải đấu cá nhân
    Route::get('/my-tournaments', [TournamentController::class, 'myTournaments'])->name('tournaments.my');

    // Thêm xóa thành viên đội
    Route::post('/players/{id}/members', [TournamentController::class, 'storeTeam'])->name('member.add');
    Route::delete('/members/{id}', [TournamentController::class, 'destroyTeam'])->name('member.delete');
});

Route::get('/tournaments/{id}', [TournamentController::class, 'show'])->name('tournament.show');
Route::post('/matches/{match}/time', [TournamentController::class, 'updateMatchTime'])->name('matches.time.update');
Route::get('/test', [TournamentController::class, 'test'])->name('test');
