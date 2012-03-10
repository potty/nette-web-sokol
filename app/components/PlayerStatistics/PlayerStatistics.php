<?php

use Nette\Application\UI;
use Nette\Database\Table\Selection;

class PlayerStatistics extends UI\Control
{
    
    private $players;
    private $model;
    
    public function __construct(Selection $players, \Model $model) 
    {
	parent::__construct();
	$this->players = $players;
	$this->model = $model;
    }
    
    
    
    /**
     * Renders Player statistics
     */
    public function render()
    {
	$this->template->setFile(__DIR__ . '/PlayerStatistics.latte');
	$stats = array();
	foreach ($this->players as $player) {
	    $starting = $this->model->getPlayersMatches()->where('player_id = ?', $player->id)->count();
	    $subs = $this->model->getSubstitutions()->where('player_in_id = ?', $player->id)->count();
	    $subs_out_count = $this->model->getSubstitutions()->where('player_out_id = ?', $player->id)->count();
	    $goals = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ?', $player->id, 'IV. třída', 'gól')->count();
	    $goals_pen = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ? AND penalty = ?', $player->id, 'IV. třída', 'gól', true)->count();
	    $subs_in = $this->model->getSubstitutions()->where('player_in_id = ?', $player->id);
	    $subs_out = $this->model->getSubstitutions()->where('player_out_id = ?', $player->id);
	    $yellow_cards = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ?', $player->id, 'IV. třída', 'žlutá karta')->count();
	    $red_cards = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ?', $player->id, 'IV. třída', 'červená karta')->count();
	    
	    $mins = 90 * $starting;
	    foreach ($subs_in as $sub) {
		$mins += (90 - $sub->minute);
	    }
	    
	    foreach ($subs_out as $sub) {
		$mins -= (90 - $sub->minute);
	    }
	    
	    $values = array(
		'matches' => $starting + $subs,
		'starting' => $starting,
		'subs_in' => $subs,
		'subs_out' => $subs_out_count,
		'goals' => $goals,
		'goals_pen' => $goals_pen,
		'mins' => $mins,
		'y_cards' => $yellow_cards,
		'r_cards' => $red_cards,
	    );
	    $stats[$player->id] = $values;
	}
	$this->template->players = $this->players;
	$this->template->stats = $stats;
	$this->template->render();
    }
    
}
