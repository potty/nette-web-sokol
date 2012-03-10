<?php

use Nette\Application\UI\Form;
use Vodacek\Forms\Controls\DateInput;

/**
 * Description of MatchPresenter
 *
 * @author Potty
 */
class MatchPresenter extends BasePresenter {

    private $match;
    private $id;
    
    public function beforeRender() {
	parent::beforeRender();
	$result = $this->model->getSeasons()->select('name')->where('id', $this->currentSeason)->fetch();
	$this->template->currentSeason = $result['name'];
	$this->template->allowedEdit = $this->acl->isAllowed($this->user->identity->roles[0], 'match', 'edit');
    }
    
    public function renderDefault() {
	// get all Veterov matches (don't know how to implement string value instead index)
	$this->template->matches = $this->model->getMatches()->where('home_id = ? OR away_id =?', 1, 1)->order('date ASC');
    }
    
    public function actionEdit($id)
    {
	$this->match = $this->model->getMatches()->find($id)->fetch();
	if ($this->match === FALSE) {
	    $this->setView('notFound');
	}
	$this->id = $id;
	$this['matchEditForm']->setDefaults($this->match);
    }
    
    public function actionSingle($id)
    {
	$this->match = $this->model->getMatches()->find($id)->fetch();
	if ($this->match === FALSE) {
	    $this->setView('notFound');
	}
    }
    
    public function actionAddSubs($id)
    {
	$this->match = $this->model->getMatches()->find($id)->fetch();
	if ($this->match === FALSE) {
	    $this->setView('notFound');
	}
	$this->id = $id;
    }
    
    public function actionAddEvent($id)
    {
	$this->match = $this->model->getMatches()->find($id)->fetch();
	if ($this->match === FALSE) {
	    $this->setView('notFound');
	}
	$this->id = $id;
    }
    
    public function renderSingle()
    {
	$this->template->match = $this->match;
    }
    
    public function renderEdit()
    {
	$this->template->matchId = $this->id;
	$this->template->events = $this->model->getEvents()->where('match_id = ?', $this->id)->order('minute ASC');
	$this->template->subs = $this->model->getSubstitutions()->where('match_id = ?', $this->id)->order('minute ASC');
    }
    
    public function renderCompetition()
    {
	$this->template->matches = $this->model->getMatches()->where('competition.name = ?', 'IV. třída')->order('date ASC');
    }
    
    protected function createComponentMatchAddForm()
    {
	$form = new Form();
	$form->addSelect('seasonId', 'Sezona:', $this->model->getSeasons()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat sezonu.');
	$form['seasonId']->setDefaultValue($this->currentSeason);
	$form->addSelect('competitionId', 'Soutěž:', $this->model->getCompetitions()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat soutěž.');
	$form->addText('round', 'Kolo:', 40, 100);
	$form->addDate('date', 'Datum:', DateInput::TYPE_DATETIME)
                                ->setRequired('Uveďte datum.');
	$form->addSelect('homeId', 'Domácí:', $this->model->getTeams()->order('name ASC')->fetchPairs('id', 'name'))
		->setPrompt('- Vyberte -')
		->addRule(Form::FILLED, 'Je nutné vybrat domácí tým.');
	$form->addSelect('awayId', 'Hosté:', $this->model->getTeams()->order('name ASC ')->fetchPairs('id', 'name'))
		->setPrompt('- Vyberte -')
		->addRule(Form::FILLED, 'Je nutné vybrat hostující tým.');
	$form->addSubmit('create', 'Vytvořit');
	$form->onSuccess[] = callback($this, 'matchAddFormSubmitted');
	return $form;
    }
    
    public function matchAddFormSubmitted(Form $form)
    {
	$data = array(
	    'competition_id' => $form->values->competitionId,
	    'date' => $form->values->date,
	    'season_id' => $form->values->seasonId,
	    'home_id' => $form->values->homeId,
	    'away_id' => $form->values->awayId,
	);
	if ($form->values->round != '') $data['round'] = $form->values->round;
	$this->model->getMatches()->insert($data);
	$this->flashMessage('Zápas přidán.', 'success');
	$this->redirect('this');
    }
    
    protected function createComponentMatchEditForm() 
    {
	$form = new Form();
	$form->addHidden('id');
	$form->addSelect('season_id', 'Sezona:', $this->model->getSeasons()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat sezonu.');
	$form->addSelect('competition_id', 'Soutěž:', $this->model->getCompetitions()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat soutěž.');
	$form->addText('round', 'Kolo:', 40, 100);
	$form->addDate('date', 'Datum:', DateInput::TYPE_DATETIME)
                                ->setRequired('Uveďte datum.');
	$form->addSelect('home_id', 'Domácí:', $this->model->getTeams()->order('name ASC')->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat domácí tým.');
	$form->addSelect('away_id', 'Hosté:', $this->model->getTeams()->order('name ASC ')->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat hostující tým.');
	$form->addText('score_home', 'Skóre domácí:')
		->setType('number')
		->addRule(Form::INTEGER, 'Skóre musí být číslo')
		->addRule(Form::RANGE, 'Skóre musí být od 0 do 20', array(0, 20));
	$form->addText('score_away', 'Skóre hosté:')
		->setType('number')
		->addRule(Form::INTEGER, 'Skóre musí být číslo')
		->addRule(Form::RANGE, 'Skóre musí být od 0 do 20', array(0, 20));
	$form->addCheckbox('played', 'Odehráno');
	$form->addSubmit('save', 'Uložit');
	$form->onSuccess[] = callback($this, 'matchEditFormSubmitted');
	return $form;
    }
    
    public function matchEditFormSubmitted(Form $form)
    {
	$data = array(
	    'competition_id' => $form->values->competition_id,
	    'date' => $form->values->date,
	    'season_id' => $form->values->season_id,
	    'home_id' => $form->values->home_id,
	    'away_id' => $form->values->away_id,
	    'played' => $form->values->played,
	);
	if ($form->values->round != '') $data['round'] = $form->values->round;
	if ($form->values->score_home != '') $data['score_home'] = $form->values->score_home;
	if ($form->values->score_away != '') $data['score_away'] = $form->values->score_away;
	$this->model->getMatches()->find($form->values->id)->update($data);
	$this->flashMessage('Zápas aktualizován.', 'success');
	$this->redirect('Match:');
    }
    
    protected function createComponentEventAddForm()
    {
	$form = new Form();
	$form->addHidden('match_id', $this->id);
	$form->addSelect('event_type_id', 'Typ:', $this->model->getEventTypes()->order('id ASC')->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné zadat typ.');
	$form->addText('minute', 'Minuta:')
		->setType('number');
	$form->addSelect('player_id', 'Hráč:', $this->fetchPairsPlayers())
		->addRule(Form::FILLED, 'Je nutné vybrat hráče.');
	$form->addCheckbox('penalty', 'Penalta');
	$form->addSubmit('save', 'Uložit');
	$form->onSuccess[] = callback($this, 'eventAddFormSubmitted');
	return $form;
    }
    
    public function eventAddFormSubmitted(Form $form)
    {
	$data = array(
	    'match_id' => $form->values->match_id,
	    'event_type_id' => $form->values->event_type_id,
	    'player_id' => $form->values->player_id,
	);
	if ($form->values->minute != '') $data['minute'] = $form->values->minute;
	if ($form->values->penalty != '') $data['penalty'] = $form->values->penalty;
	$this->model->getEvents()->insert($data);
	$this->flashMessage('Událost přidána.', 'success');
	$this->redirect('Match:edit', $form->values->match_id);
    }
    
    protected function createComponentSubstitutionAddForm()
    {
	$form = new Form();
	$form->addHidden('match_id', $this->id);
	$form->addText('minute', 'Minuta:')
		->setType('number')
		->addRule(Form::INTEGER, 'Minuta musí být číslo')
		->addRule(Form::RANGE, 'Minuta musí být od 1 do 90', array(1, 90));
	$form->addSelect('player_in_id', 'Hráč do hry:', $this->fetchPairsPlayers())
		->addRule(Form::FILLED, 'Je nutné vybrat hráče.');
	$form->addSelect('player_out_id', 'Hráč ze hry:', $this->fetchPairsPlayers())
		->addRule(Form::FILLED, 'Je nutné vybrat hráče.');
	$form->addSubmit('save', 'Uložit');
	$form->onSuccess[] = callback($this, 'substitutionAddFormSubmitted');
	return $form;
    }
    
    public function substitutionAddFormSubmitted(Form $form)
    {
	$data = array(
	    'match_id' => $form->values->match_id,
	    'player_in_id' => $form->values->player_in_id,
	    'player_out_id' => $form->values->player_out_id,
	    'minute' => $form->values->minute,
	);
	$this->model->getSubstitutions()->insert($data);
	$this->flashMessage('Střídání přidáno.', 'success');
	$this->redirect('Match:edit', $form->values->match_id);
    }
    
    /**
     * Returns array of pairs 'id' => 'player_surname player_name' 
     * @return array 
     */
    private function fetchPairsPlayers()
    {
	$array = array();
	$players = $this->model->getPlayers()->where('team.name = ?', 'Věteřov')->order('surname ASC');
	foreach ($players as $player) {
	    $array[$player->id] = $player->surname . ' ' . $player->name;
	}
	return $array;
    }
    
    /**
     * Creates component PlayerList of Veterov players
     * @return PlayerList 
     */
    protected function createComponentStartingEleven()
    {
	$db = $this->context->database;
	$players = $this->model->getPlayers()->where('team.name', 'Věteřov')->order('surname ASC', 'name ASC');
	return new StartingEleven($players, $this->id, $this->model, $db);
    }

}