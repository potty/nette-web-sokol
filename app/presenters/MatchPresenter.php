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
    
    public function renderDefault() {
	$this->template->matches = $this->model->getMatches()->order('date ASC');
	$result = $this->model->getSeasons()->select('name')->where('id', $this->currentSeason)->fetch();
	$this->template->currentSeason = $result['name'];
	$this->template->allowedEdit = $this->acl->isAllowed($this->user->identity->roles[0], 'match', 'edit');
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
    
    public function renderSingle()
    {
	$this->template->match = $this->match;
    }
    
    protected function createComponentMatchAddForm()
    {
	$form = new Form();
	$form->addSelect('seasonId', 'Sezona:', $this->model->getSeasons()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat sezonu.');
	$form['seasonId']->setDefaultValue($this->currentSeason);
	$form->addSelect('competitionId', 'Soutěž:', $this->model->getCompetitions()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat soutěž.');
	$form->addText('round', 'Kolo:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat kolo.');
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
	$form->addText('round', 'Kolo:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat kolo.');
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
	if ($form->values->round != '') $data['score_home'] = $form->values->score_home;
	if ($form->values->round != '') $data['score_away'] = $form->values->score_away;
	$this->model->getMatches()->find($form->values->id)->update($data);
	$this->flashMessage('Zápas aktualizován.', 'success');
	$this->redirect('Match:');
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
    
    protected function createComponentAddPlayerToMatchForm()
    {
	$form = new Form();
	$form->addSelect('player_id', 'Hráč', $this->fetchPairsPlayers())
		->addRule(Form::FILLED, 'Je nutné vybrat hráče.');
	$form->addSubmit('add', 'Přidat');
	$form->onSuccess[] = callback($this, 'addPlayerToMatchFormSubmitted');
	return $form;
    }
    
    public function addPlayerToMatchFormSubmitted(Form $form)
    {
	if ($this->model->getPlayersMatches()->where('match_id', $this->matchId)->count('*') < 11) {
	    $data = array(
		'player_id' => $form->values->player_id,
		'match_id' => $this->match->id,
	    );
	    $this->model->getPlayersMatches()->insert($data);
	    if (!$this->isAjax()) {
		$this->redirect('this');
	    } else {
		$form->setValues(array(), true);
		$this->invalidateControl('form');
		$this['startingEleven']->invalidateControl();
	    }
	} else {
	    $this->flashMessage('Nelze přidat více hráčů než 11!', 'error');  
	}
    }
    
    /**
     * Creates component PlayerList of Veterov players
     * @return PlayerList 
     */
    protected function createComponentStartingEleven()
    {
	$players = $this->model->getPlayersMatches()->where('match_id', $this->id)->order('player.surname ASC');
	return new StartingEleven($players, $this->id, $this->model);
    }

}