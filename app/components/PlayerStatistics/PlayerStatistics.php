<?php

use Nette\Application\UI;
use Nette\Database\Table\Selection;

class PlayerStatistics extends UI\Control
{
    
    private $players;
    private $model;
    private $season;
    
    public function __construct(Selection $players, \Model $model, $season) 
    {
	parent::__construct();
	$this->players = $players;
	$this->model = $model;
	$this->season = $season;
    }
    
    
    
    /**
     * Renders Player statistics
     */
    public function render()
    {
	$this->template->setFile(__DIR__ . '/PlayerStatistics.latte');
	$stats = array();
	foreach ($this->players as $player) {
	    $starting = $this->model->getPlayersMatches()->where('player_id = ? AND match.season_id = ?', $player->id, $this->season)->count();
	    $subs = $this->model->getSubstitutions()->where('player_in_id = ? AND match.season_id = ?', $player->id, $this->season)->count();
	    $subs_out_count = $this->model->getSubstitutions()->where('player_out_id = ? AND match.season_id = ?', $player->id, $this->season)->count();
	    $goals = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ? AND match.season_id = ?', $player->id, 'IV. třída', 'gól', $this->season)->count();
	    $goals_pen = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ? AND penalty = ? AND match.season_id = ?', $player->id, 'IV. třída', 'gól', true, $this->season)->count();
	    $subs_in = $this->model->getSubstitutions()->where('player_in_id = ? AND match.season_id = ?', $player->id, $this->season);
	    $subs_out = $this->model->getSubstitutions()->where('player_out_id = ? AND match.season_id = ?', $player->id, $this->season);
	    $yellow_cards = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ? AND match.season_id = ?', $player->id, 'IV. třída', 'žlutá karta', $this->season)->count();
	    $red_cards_count = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ? AND match.season_id = ?', $player->id, 'IV. třída', 'červená karta', $this->season)->count();
	    $red_cards = $this->model->getEvents()->where('player_id = ? AND match.competition.name = ? AND event_type.name = ? AND match.season_id = ?', $player->id, 'IV. třída', 'červená karta', $this->season);
	    $assists = $this->model->getEvents()->where('assist = ? AND match.competition.name = ? AND event_type.name = ? AND match.season_id = ?', $player->id, 'IV. třída', 'gól', $this->season)->count();
	    
	    $mins = 90 * $starting;
	    foreach ($subs_in as $sub) {
		$mins += (90 - $sub->minute);
	    }
	    
	    foreach ($subs_out as $sub) {
		$mins -= (90 - $sub->minute);
	    }
	    
	    foreach ($red_cards as $card) {
		$mins -= (90 - $card->minute);
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
		'r_cards' => $red_cards_count,
		'assists' => $assists,
	    );
	    $stats[$player->id] = $values;
	}
	$this->template->players = $this->players;
	$this->template->stats = $stats;
	$this->template->render();
    }
    
}
