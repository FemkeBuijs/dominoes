<?php
/**
 * Dominoes
 *
 * Class which plays an entire game of dominoes by itself.
 *
 * @since 1.0.0
 * @author Femke Buijs (@FemkeBuijs)
 * @link https://github.com/FemkeBuijs/dominoes
 */

$game = new Game();
$game->initGame();
$game->playGame();

class Game {
  private $tiles = [];
  private $board = [];
  private $currentPlayerKey = 0;
  private $currentPlayer = '';
  private $players = ['Femke', 'Maartje'];
  private $drawCount = 0;
  private $end = false;

  /**
   * Initiliases the game.
   *
   * Calls functions that will set up the game, like creating the tiles, shuffling
   * them, give each player their respective tiles and play the first tile.
   */
  public function initGame() {
    $this->prepareTiles();
    $this->shuffleTiles();
    $this->pickInitialTiles();
    $this->board[] = $this->drawTile();
  }

  /**
   * Prepares the tiles
   *
   * Creates all the tiles based on the a range of zero to six spots on each
   * side of a tile.
   */
  private function prepareTiles() {
    $array = range(0,6);

    forEach($array as $i) {
      forEach(range(0, $i) as $j) {
        $this->tiles[] = [$i, $j];
      }
    }
  }

  /**
   * Shuffles the tiles.
   */
  private function shuffleTiles() {
    shuffle($this->tiles);
  }

  /**
   * Picks all intial tiles.
   *
   * Picks initial tiles for each player (one at a time) based on each
   * player picking seven tiles.
   */
  private function pickInitialTiles() {
    $array = range(0,6);

    forEach($array as $i) {
      forEach($this->players as $player) {
        $this->{$player}[] = $this->drawTile();
      }
    }
  }

  /**
   * Draws a tile.
   *
   * Draws a random tile from the array of tiles.
   * @return Array The drawn tile.
   */
  private function drawTile() {
    $key = mt_rand(0, count($this->tiles) - 1);
    $tile = $this->tiles[$key];
    array_splice($this->tiles, $key, 1);

    return $tile;
  }

  /**
   * Plays the game.
   *
   * Chooses the first player at random and plays rounds until the game has come
   * to an end.
   */
  public function playGame() {
    $this->currentPlayerKey = mt_rand(0, count($this->players) - 1);
    $this->currentPlayer = $this->players[$this->currentPlayerKey];
    $this->write('Game starting with first tile: ' . $this->stringifyTile($this->board[0]) . '.');

    do {
      $this->playRound();
    } while (!$this->end);
  }

  /**
   * Plays a round.
   *
   * Checks whether the current player can play a matching tile, if so checks
   * whether the current player has won.
   * If not, lets the current player draw a tile if available.
   * It then selects the next player if the game has not come to an end yet.
   */
  private function playRound() {
    $matchingTile = $this->checkMatchingTile();

    // If there is a matching tile.
    if ($matchingTile) {
      list(
        'playedTile'  => $playedTile,
        'boardTile'   => $boardTile,
        'key'         => $key
      ) = $matchingTile;
      $this->drawCount = 0;
      // Remove the tile from the current players' array.
      array_splice($this->{$this->currentPlayer}, $key, 1);

      $this->write($this->currentPlayer . ' plays ' . $this->stringifyTile($playedTile)
      . ' to connect to tile ' . $this->stringifyTile($boardTile) . ' on the board.');
      $this->printBoard();

      // If the current player has no more tiles left he/she won.
      if (empty($this->{$this->currentPlayer})) {
        $this->write('Player ' . $this->currentPlayer . ' has won!');
        return $this->end = true;
      }
    } else {
      // Else draw a tile if there are any left.
      if (!empty($this->tiles)) {
        $drawnTile = $this->drawTile();
        $this->{$this->currentPlayer}[] = $drawnTile;
        $this->write($this->currentPlayer . ' cannot play and draws tile '
        . $this->stringifyTile($drawnTile) . '.');
      } else {
        $this->drawCount++;
        $this->write($this->currentPlayer
        . ' cannot play. There are no more tiles left to draw. Next player\'s turn.');

        // If there are no more tiles left to draw and no player more players
        // are able to play, the game ends in a draw. This is counted by the
        // amount of drawCounts.
        if ($this->drawCount >= count($this->players)) {
          $this->blockedGameCount();
          return $this->end = true;
        }
      }
    }

    // Switch to the next player.
    list(
      'player'  => $this->currentPlayer,
      'key'     => $this->currentPlayerKey
    ) = $this->getNextPlayer();
  }

  /**
   * Checks whether there is a matching tile.
   *
   * Checks whether the current player can play one of its tiles on the board.
   * @return Array Matching tile when found, otherwise returns False.
   */
  private function checkMatchingTile() {
    $topTile    = end($this->board);
    $bottomTile = reset($this->board);
    $currentSet = $this->{$this->currentPlayer};

    forEach($currentSet as $key => $tile) {
      // Check whether the tile matches the bottom value.
      if (in_array($bottomTile[0], $tile)) {
        // Reverse the tile if necessary
        if ($bottomTile[0] === $tile[0]) $tile = array_reverse($tile);
        array_unshift($this->board , $tile);

        return [
          'playedTile'  => $tile,
          'boardTile'   => $bottomTile,
          'key'         => $key
        ];
      // Check whether the tile matches the top value.
      } elseif (in_array($topTile[1], $tile)) {
        // Reverse the tile if necessary
        if ($topTile[1] === $tile[1]) $tile = array_reverse($tile);
        array_push($this->board , $tile);

        return [
          'playedTile'  => $tile,
          'boardTile'   => $topTile,
          'key'         => $key
        ];
      }
    }

    return false;
  }

  /**
   * Gets the next player.
   *
   * Returns the next player in the array. If it is already the last player in the
   * array, it returns the first player of the array.
   * @return Array The name and key of the next player.
   */
  private function getNextPlayer() {
    // Check if player is the last player in the array.
    end($this->players);
    if ($this->currentPlayerKey >= key($this->players)) {
      // If so, return the first player.
      return [
        'player'  => reset($this->players),
        'key'     => key($this->players)
      ];
    }
    reset($this->players);

    // Otherwise return the next player.
    $currentKey = key($this->players);
    while ($currentKey !== null && $currentKey != $this->currentPlayerKey) {
        next($this->players);
        $currentKey = key($this->players);
    }

    return [
      'player'  => next($this->players),
      'key'     => key($this->players)
    ];
  }

  /**
   * Prints the current status of the board.
   */
  private function printBoard() {
    $textedTiles = '';
    foreach($this->board as $tile) $textedTiles .= $this->stringifyTile($tile);
    $this->write('Board is now ' . $textedTiles . '.');
  }

  /**
   * Shows the game progress by means of stdout.
   * @param String $text The text to be printed.
   */
  private function write($text) {
    fwrite(STDOUT, $text . PHP_EOL);
  }

  /**
   * Creates a string from a given tile array.
   * @param Array $tile The tile to be stringified.
   * @return String The stringified tile.
   */
  private function stringifyTile($tile) {
    return '<' . $tile[0] . ':' . $tile[1] . '>';
  }

  /**
   * Counts the spots when the game is blocked.
   *
   * Determines who wins when the game is blocked, meaning no more players
   * can play. This is done by counting the spots of each of the tiles left
   * and the player with the lowest amount of spots in his/her hand wins.
   */
  private function blockedGameCount() {
    $totals = [];

    forEach($this->players as $player) {
      $sum = 0;
      forEach($this->{$player} as $tile) {
        $sum += array_sum($tile);
      }
      $totals[$player] = $sum;
    }

    $winner = array_keys($totals, min($totals));
    if(count($winner) === count($this->players)) $this->write('Game ends in draw.');
    else $this->write('The game is blocked. '. reset($winner) . ' won by having the most spots.');
  }
}
