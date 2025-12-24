<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'mode',
        'max_player',
        'status',
        'creator_id',
        'category',
        'game_name',
        'start_date',
        'thumbnail',
    ];

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function matches()
    {
        return $this->hasMany(Matches::class, 'tournament_id');
    }
}
