<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameFinalized
{
    use Dispatchable, SerializesModels;

    public Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }
}