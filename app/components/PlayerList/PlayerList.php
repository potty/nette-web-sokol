<?php

use Nette\Application\UI;
use Nette\Database\Table\Selection;

class PlayerList extends UI\Control
{
    
    private $players;
    private $model;
    private $trainingId;
    
    public function __construct(Selection $players, $trainingId, \Model $model) 
    {
	parent::__construct();
	$this->players = $players;
	$this->model = $model;
	$this->trainingId = $trainingId;
    }
    
    /**
     * Renders Player List
     */
    public function render()
    {
	$this->template->setFile(__DIR__ . '/PlayerList.latte');
	$this->template->players = $this->players;
	// Prochazeni vsech hracu a kontrola, zda se daneho treninku zucastnili
	// Predava sablone asoc. pole s klicem player_id a hodnotou bool, zda se ucastnil
	$isParticipate = array();
	foreach ($this->players as $player) {
	    $count = $this->model->getPlayersTrainings()->where('training_id', $this->trainingId)->where('player_id', $player->id)->count();
	    if ($count == 0) {
		$isParticipate[$player->id] = FALSE; 
	    } else {
		$isParticipate[$player->id] = TRUE;
	    }
	}
	$this->template->trainingId = $this->trainingId;
	$this->template->isParticipate = $isParticipate;
	$this->template->render();
    }
    
    /**
     * Adds or removes players from training
     * @param int $trainingId
     * @param int $playerId
     * @param boolean $isParticipate 
     * @return void
     */
    public function handleParticipate($trainingId, $playerId, $isParticipate)
    {
	if (!$isParticipate) {
	    // Kdyz hrac jeste neni pridan, tak se vytvori zaznam
	    $this->model->getPlayersTrainings()->insert(array('player_id' => $playerId, 'training_id' => $trainingId));
	    //$this->presenter->flashMessage('Hráč přidán do tréninku.');
	} else {
	    // Kdyz zaznam existuje, tak se odebere
	    $this->model->getPlayersTrainings()->where('player_id = ? AND training_id = ?', $playerId, $trainingId)->delete();
	    //$this->presenter->flashMessage('Hráč odebrán z tréninku.');
	}
	if ($this->presenter->isAjax()) {
	    $this->invalidateControl();
	} else {
	    $this->presenter->redirect('this');
	}
    }
    
}
