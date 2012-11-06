<?php

use Nette\Application\UI\Form;
use Nette\Image;

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
	private $seasonName;
	
	private $season = NULL;
    
    
    
	public function beforeRender()
	{
		parent::beforeRender();
		$result = $this->model->getSeasons()->select('name')->where('id', $this->currentSeason)->fetch();
		$this->seasonName = $result['name'];
		$this->template->allowedViewTraining = $this->isUserAllowedToAction('training', 'default');
	}
	
	
    
	public function renderDefault()
	{
		$this->template->goalkeepers = $this->getPlayersByFilter('Věteřov', 'brankář');
		$this->template->defenders = $this->getPlayersByFilter('Věteřov', 'obránce');
		$this->template->midfielders = $this->getPlayersByFilter('Věteřov', 'záložník');
		$this->template->forwards = $this->getPlayersByFilter('Věteřov', 'útočník');
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
	$teamId = $this->model->getTeamsPlayers()->select('team_id')->where('player_id = ? AND season_id = ?', $this->player->id, $this->currentSeason)->fetch();
	$this->template->team = $this->model->getTeams()->find($teamId['team_id'])->fetch();
	$matches = $this->model->getMatches()
		->where('(home_id = ? OR away_id = ?) AND played = ? AND competition.name = ? AND season_id = ?', $teamId['team_id'], $teamId['team_id'], true, 'IV. třída', $this->currentSeason)
		->order('date ASC');
	
	$minutes = array();
	$goals = array();
	$assists = array();
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
		$assists[$match->id] = $this->model->getEvents()->where('match_id = ? AND assist = ? AND event_type.name = ?', $match->id, $this->player->id, 'gól')->count();
		$yellow_cards[$match->id] = $this->model->getEvents()->where('match_id = ? AND player_id = ? AND event_type.name = ?', $match->id, $this->player->id, 'žlutá karta')->count();
		$red_cards[$match->id] = $this->model->getEvents()->where('match_id = ? AND player_id = ? AND event_type.name = ?', $match->id, $this->player->id, 'červená karta')->count();
	    } else {
		$minutes[$match->id] = '-';
		$goals[$match->id] = '-';
		$assists[$match->id] = '-';
		$yellow_cards[$match->id] = '-';
		$red_cards[$match->id] = '-';
	    }
	    
	}
	
	// trainings
	$training_total = 0;
	$training_part_num = 0;
	$percentage = 0;
	$participating = array();
	$trainings = $this->model->getTrainings()->where('season_id = ?', $this->currentSeason)->order('date DESC');
	foreach ($trainings as $training) {
	    $training_total++;
	    $count = $this->model->getPlayersTrainings()->where('player_id = ? AND training_id = ?', $this->player->id, $training->id)->count();
	    if ($count > 0) {
		$training_part_num++;
		$participating[$training->id] = true;
	    } else {
		$participating[$training->id] = false;
	    }
	}
	if ($training_part_num != 0) $percentage = ($training_part_num / $training_total) * 100;
	
	$this->template->yellow_cards = $yellow_cards;
	$this->template->red_cards = $red_cards;
	$this->template->minutes = $minutes;
	$this->template->goals = $goals;
	$this->template->assists = $assists;
	$this->template->matches = $matches;
	$this->template->currentSeason = $this->seasonName;
	$this->template->trainings = $trainings;
	$this->template->trainingParticipate = $participating;
	$this->template->trainingsTotal = $training_total;
	$this->template->trainingsPartNum = $training_part_num;
	$this->template->trainingsPercentage = $percentage;
    }
    
    
    
	public function renderStatistics($season = NULL)
	{
		if ($season == NULL) {
			$selectedSeason = $this->currentSeason;
		} else {
			$selectedSeason = $season;
		}
		$this->season = $season;
		$result = $this->model->getSeasons()->select('name')->where('id', $selectedSeason)->fetch();
		$this->template->season = $result['name'];
	}

    
    
	/**
	 * Add player form
	 * @return Form 
	 */
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
    
	
    
	/**
	 * Add player process
	 * @param Form $form 
	 */
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
		if ($this->season != NULL) {
			$season = $this->season;
		} else {
			$season = $this->currentSeason;
		}
		$players = $this->model->getPlayers()
			->where('player.id', $this->model->getTeamsPlayers()->select('player_id')->where('season_id = ? AND team.name = ?', $season, 'Věteřov'))
			->order('surname ASC', 'name ASC');
		return new PlayerStatistics($players, $this->model, $season);
	}
    
    
    
	/**
	 * Player registration to team
	 * @return Form 
	 */
	protected function createComponentRegisterPlayer()
	{
		$form = new Form();
	    
		$form->addSelect('playerId', 'Hráč: ', $this->fetchPairsPlayers(TRUE))
			->setRequired();
	    
		$form->addSelect('teamId', 'Tým: ', $this->model->getTeams()->order('name ASC')->fetchPairs('id', 'name'))
			->setDefaultValue(1)
			->setRequired();
	    
		$form->addSelect('seasonId', 'Sezona: ', $this->model->getSeasons()->order('start_date DESC')->fetchPairs('id', 'name'))
			->setRequired();
	    
		$form->addSubmit('save', 'Uložit');
	    
		$form->onSuccess[] = callback($this, 'registerPlayerSubmitted');
	    
		return $form;
	}
	
	
	
	/**
	 * Register player process
	 * @param Form $form 
	 */
	public function registerPlayerSubmitted(Form $form)
	{
		$values = $form->values;
		
		$data = array (
		    'player_id' => $values->playerId,
		    'team_id' => $values->teamId,
		    'season_id' => $values->seasonId,
		);
		
		$this->model->getTeamsPlayers()->insert($data);
		$this->flashMessage('Hráč zaregistrován.', 'success');
		$this->redirect('Admin:playerRegister');
	}
	
	
	
	/**
	 * Season select form
	 * @return SeasonForm 
	 */
	protected function createComponentSeasonForm()
	{
	    $form = new SeasonForm($this->model);
	    if ($this->season == NULL) {
		    $form['seasonId']->setDefaultValue($this->currentSeason);
	    } else {
		    $form['seasonId']->setDefaultValue($this->season);
	    }
	    return $form;
	}
	
	

}