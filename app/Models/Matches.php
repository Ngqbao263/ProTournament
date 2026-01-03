<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    protected $fillable = [
        'tournament_id', 'player1_id', 'player2_id',
        'score1', 'score2', 'winner_id', 'round_number', 'match_index', 'match_time', 'group'
    ];

    public function player1() { return $this->belongsTo(Player::class, 'player1_id'); }
    public function player2() { return $this->belongsTo(Player::class, 'player2_id'); }
    public function winner()  { return $this->belongsTo(Player::class, 'winner_id'); }

    protected $casts = [
        'match_time' => 'datetime',
    ];
}
