<?php

$game = new Game();
$game->initGame();
$game->playGame();

class Game {
  private $tiles = [];
  private $line = [];
  private $playerOne = [];
  private $playerTwo = [];
  private $currentPlayer = '';
  private $players = ['playerOne', 'playerTwo'];

  private $end = true;

  public function initGame() {
    $this->prepareTiles();
    $this->shuffleTiles();
    $this->pickInitialTiles();
    $this->line[] = $this->drawTile();
  }

  public function playGame() {
    $this->currentPlayer = $this->players[mt_rand(0, count($this->players) - 1)];
    // fwrite(STDOUT, 'Game starting with first tile: ' . $this->stringifyTile($this->line[0]));

    do {
      $this->playRound();
    } while (!$this->end);
  }

  private function playRound() {
    if ($this->checkMatchingTile()) {

    }
  }

  public function checkMatchingTile() {
    $bottomValue = $this->line[0][0];
    $topValue = $this->line[count($this->line) -1][1];
    $currentSet = $this->{$this->currentPlayer};

    forEach($currentSet as $key => $tile) {
      if (in_array($bottomValue, $tile)) {

        if ($bottomValue === $tile[0]) {
          $tile = array_reverse($tile);
        }

        array_splice($this->{$this->currentPlayer}, $key, 1);
        array_unshift($this->line , $tile);
        return true;
      } elseif (in_array($topValue, $tile)) {

        if ($topValue === $tile[1]) {
          $tile = array_reverse($tile);
        }

        array_splice($this->{$this->currentPlayer}, $key, 1);
        array_push($this->line , $tile);
        return true;
      }
    }

    return false;
  }

  private function prepareTiles() {
    $array = range(0,6);

    forEach($array as $i) {
      forEach(range(0, $i) as $j) {
        $this->tiles[] = [$i, $j];
      }
    }
  }

  private function shuffleTiles() {
    shuffle($this->tiles);
  }

  public function pickInitialTiles() {
    $array = range(0,6);
    forEach($array as $i) {
      forEach($this->players as $player) {
        $this->{$player}[] = $this->drawTile();
      }
    }
  }

  private function drawTile() {
    $key = mt_rand(0, count($this->tiles) - 1);
    $tile = $this->tiles[$key];
    array_splice($this->tiles, $key, 1);

    return $tile;
  }

  public function stringifyTile($tile) {
    return '<' . $tile[0] . ':' . $tile[1] . '>';
  }
}
