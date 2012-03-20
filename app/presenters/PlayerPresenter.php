<?php

use Nette\Application\UI\Form;

/**
 * Description of PlayerPresenter
 *
 * @author Potty
 */
class PlayerPresenter extends BasePresenter {

    private $player;
    
    /**
     * Current season name (e.g. 2011/2012)
     * @var string 
     */
    private $season;
    
    public function beforeRender() {
	parent::beforeRender();
	$result = $this->model->getSeasons()->select('name')->where('id', $this->currentSeason)->fetch();
	$this->season = $result['name'];
    }
    
    public function renderDefault()
    {
	$this->template->goalkeepers = $this->model->getPlayers()->where('team.name', 'Věteřov')->where('position.name', 'brankář')->order('surname ASC', 'name ASC');
	$this->template->defenders = $this->model->getPlayers()->where('team.name', 'Věteřov')->where('position.name', 'obránce')->order('surname ASC', 'name ASC');
	$this->template->midfielders = $this->model->getPlayers()->where('team.name', 'Věteřov')->where('position.name', 'záložník')->order('surname ASC', 'name ASC');
	$this->template->forwards = $this->model->getPlayers()->where('team.name', 'Věteřov')->where('position.name', 'útočník')->order('surname ASC', 'name ASC');
    }
    
    public function actionSingle($id)
    {
	$this->player = $this->model->getPlayers()->find($id)->fetch();
	if ($this->player === FALSE) {
	    $this->setView('notFound');
	}
    }
    
    public function renderSingle()
    {
	$this->template->player = $this->player;
	$matches = $this->model->getMatches()
		->where('(home_id = ? OR away_id = ?) AND played = ? AND competition.name = ? AND season.name = ?', $this->player->team_id, $this->player->team_id, true, 'IV. třída', $this->season)
		->order('date ASC');
	
	$minutes = array();
	$goals = array();
	$yellow_cards = array();
	$red_cards = array();
	foreach ($matches as $match) {
	    $mins = 0;
	    $starting = $this->model->getPlayersMatches()->where('match_id = ? AND player_id = ?', $match->id, $this->player->id)->count();
	    // if player played at least 1 match
	    if ($starting > 0) {
		$mins = 90;
		$result = $this->model->getSubstitutions()->select('minute')->where('match_id = ? AND player_out_id = ?', $match->id, $this->player->id)->fetch();
		if ($result) $mins -= (90 - $result['minute']);
	    } else {
		$result = $this->model->getSubstitutions()->select('minute')->where('match_id = ? AND player_in_id = ?', $match->id, $this->player->id)->fetch();
		if ($result) $mins = (90 - $result['minute']);
	    }
	    // if number of minutes in match > 0
	    if ($mins != 0) {
		$red = $this->model->getEvents()->where('match_id = ? AND player_id = ? AND event_type.name = ?', $match->id, $this->player->id, 'červená karta')->fetch();
		// if player received red card, minutes are subtracted
		if ($red)
		    $mins -= (90 - $red['minute']);
		$minutes[$match->id] = $mins;
		$goals[$match->id] = $this->model->getEvents()->where('match_id = ? AND player_id = ? AND event_type.name = ?', $match->id, $this->player->id, 'gól')->count();
		$yellow_cards[$match->id] = $this->model->getEvents()->where('match_id = ? AND player_id = ? AND event_type.name = ?', $match->id, $this->player->id, 'žlutá karta')->count();
		$red_cards[$match->id] = $this->model->getEvents()->where('match_id = ? AND player_id = ? AND event_type.name = ?', $match->id, $this->player->id, 'červená karta')->count();
	    } else {
		$minutes[$match->id] = '-';
		$goals[$match->id] = '-';
		$yellow_cards[$match->id] = '-';
		$red_cards[$match->id] = '-';
	    }
	    
	}
	$this->template->yellow_cards = $yellow_cards;
	$this->template->red_cards = $red_cards;
	$this->template->minutes = $minutes;
	$this->template->goals = $goals;
	$this->template->matches = $matches;
	$this->template->currentSeason = $this->season;
    }
    
    public function renderStatistics()
    {
	
    }

    protected function createComponentPlayerAddForm()
    {
	$form = new Form();
	$form->addText('name', 'Jméno:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat jméno.');
	$form->addText('surname', 'Příjmení:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat příjmení.');
	$form->addText('birth', 'Datum narození:', 40, 100);
	$form->addSelect('positionId', 'Pozice:', $this->model->getPositions()->fetchPairs('id', 'name'))
		->setPrompt('- Vyberte -')
		->addRule(Form::FILLED, 'Je nutné vybrat pozici.');
	$form->addSelect('teamId', 'Tým:', $this->model->getTeams()->order('name ASC')->fetchPairs('id', 'name'))
		->setPrompt('- Vyberte -');
	$form->addText('photo', 'Foto:', 40, 100);
	$form->addSubmit('create', 'Vytvořit');
	$form->onSuccess[] = callback($this, 'playerAddFormSubmitted');
	return $form;
    }
    
    public function playerAddFormSubmitted(Form $form)
    {
	$data = array(
	    'name' => $form->values->name,
	    'surname' => $form->values->surname,
	    'position_id' => $form->values->positionId,
	    'team_id' => $form->values->teamId,
	);
	if ($form->values->birth != '') $data['birth'] = $form->values->birth;
	if ($form->values->photo != '') $data['photo'] = $form->values->photo;
	$this->model->getPlayers()->insert($data);
	$this->flashMessage('Hráč přidán.', 'success');
	$this->redirect('this');
    }
    
    /**
     * Creates component PlayerStatistics
     * @return PlayerList 
     */
    protected function createComponentPlayerStatistics()
    {
	$players = $this->model->getPlayers()->where('team.name', 'Věteřov')->order('surname ASC', 'name ASC');
	return new PlayerStatistics($players, $this->model);
    }

}