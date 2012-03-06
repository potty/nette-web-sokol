<?php

use Nette\Application\UI;
use Nette\Database\Table\Selection;

class StartingEleven extends UI\Control 
{

    private $players;
    private $model;
    private $matchId;
    private $db;
    
    public function __construct(Selection $players, $matchId, \Model $model, $db) 
    {
	parent::__construct();
	$this->players = $players;
	$this->model = $model;
	$this->matchId = $matchId;
	$this->db = $db;
    }
    
    /**
     * Renders Player List
     */
    public function render()
    {
	$this->template->setFile(__DIR__ . '/StartingEleven.latte');
	$this->template->players = $this->players;
	// Prochazeni vsech hracu a kontrola, zda se daneho treninku zucastnili
	// Predava sablone asoc. pole s klicem player_id a hodnotou bool, zda se ucastnil
	$isParticipate = array();
	$numbers = array();
	foreach ($this->players as $player) {
	    $count = $this->model->getPlayersMatches()->where('match_id', $this->matchId)->where('player_id', $player->id)->count();
	    if ($count == 0) {
		$isParticipate[$player->id] = FALSE; 
	    } else {
		$isParticipate[$player->id] = TRUE;
		$result = $this->model->getPlayersMatches()->select('number')->where('player_id = ? AND match_id = ?', $player->id, $this->matchId)->fetch();
		$numbers[$player->id] = $result['number'];
	    }
	}
	$this->template->counter = $this->model->getPlayersMatches()->where('match_id', $this->matchId)->count('*');
	$this->template->isParticipate = $isParticipate;
	$this->template->matchId = $this->matchId;
	$this->template->numbers = $numbers;
	$this->template->render();
    }
    
    private function availableNumber($matchId)
    {
	$rows = $this->db->query('SELECT number FROM player_match WHERE match_id = ?', $matchId);
	$numbers = array();
	foreach ($rows as $row) {
	    $numbers[] = $row['number'];
	}
	$return = true;
	for ($i = 1; $i < 12; $i++) {
	    if (!in_array($i, $numbers)) {
		return $i;
	    }
	}
	return 0;
    }
    
    /**
     * Adds or removes players from match
     * @param int $matchId
     * @param int $playerId
     * @param boolean $isParticipate 
     * @return void
     */
    public function handleParticipate($matchId, $playerId, $isParticipate)
    {
	// checks if player is participating in match
	if (!$isParticipate) {
	    // chcecks if match don't have 11 players participating
	    if ($this->model->getPlayersMatches()->where('match_id', $matchId)->count('*') < 11) {
		// if match has <11 players, player will be added
		$this->model->getPlayersMatches()->insert(array('player_id' => $playerId, 'match_id' => $matchId, 'number' => $this->availableNumber($matchId)));
	    } else {
		// otherwise nothing will happen
		$this->presenter->flashMessage('Nelze přidat více hráčů než 11!', 'error');
	    }
	} else {
	    // Kdyz zaznam existuje, tak se odebere
	    $this->model->getPlayersMatches()->where('player_id = ? AND match_id = ?', $playerId, $matchId)->delete();
	}
	if ($this->presenter->isAjax()) {
	    $this->invalidateControl();
	} else {
	    $this->presenter->redirect('this');
	}
    }
    
}