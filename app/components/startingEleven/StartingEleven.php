<?php

use Nette\Application\UI;
use Nette\Database\Table\Selection;

class StartingEleven extends UI\Control 
{

    private $players;
    private $model;
    private $matchId;
    
    public function __construct(Selection $players, $matchId, \Model $model) 
    {
	parent::__construct();
	$this->players = $players;
	$this->model = $model;
	$this->matchId = $matchId;
    }
    
    /**
     * Renders Player List
     */
    public function render()
    {
	$this->template->setFile(__DIR__ . '/StartingEleven.latte');
	$this->template->players = $this->players;
	$this->template->counter = $this->model->getPlayersMatches()->where('match_id', $this->matchId)->count('*');
	$this->template->render();
    }
    
}