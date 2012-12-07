<?php

use Nette\Application\UI\Form;

/**
 * Description of TeamPresenter
 *
 * @author Potty
 */
class TeamPresenter extends BasePresenter {

    private $team;
    
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
    
    public function actionSingle($id)
    {
	$this->team = $this->model->getTeams()->find($id)->fetch();
	if ($this->team === FALSE) {
	    $this->setView('notFound');
	}
    }
    
    public function renderSingle()
    {
	$this->template->team = $this->team;
	$matches = $this->model->getMatches()
		->where('(home_id = ? OR away_id = ?) AND season.name = ? AND played = ?', $this->team->id, $this->team->id, $this->season, TRUE)
		->order('date ASC');
	$this->template->currentSeason = $this->season;
	$results = array();
	foreach ($matches as $match) {
	    $status = 'lose';						    // default status lose
	    if ($match->home_id == $this->team->id) {
		if ($match->score_home > $match->score_away) {		    // home wins
		    $status = 'win';
		} else if ($match->score_home == $match->score_away) {	    // home draws
		    $status = 'draw';
		}
	    } else if ($match->score_home < $match->score_away) {	    // away wins
		$status = 'win';
	    } else if ($match->score_home == $match->score_away) {	    // away draws
		$status = 'draw';
	    }
	    if ($match->played == false)				    // match haven't been played yet 
		$status = 'not-played';
	    $results[$match->id] = $status;
	}
	$this->template->results = $results;
	$this->template->matches = $matches;
    }
    
    
    
	/**
	 * Team registration to competition
	 * @return Form 
	 */
	protected function createComponentRegisterTeam()
	{
		$form = new Form();
	    
		$form->addSelect('teamId', 'Tým: ', $this->model->getTeams()->order('name ASC')->fetchPairs('id', 'name'))
			->setRequired();
	    
		$form->addSelect('competitionId', 'Soutěž: ', $this->model->getCompetitions()->order('id ASC')->fetchPairs('id', 'name'))
			->setRequired();
	    
		$form->addSelect('seasonId', 'Sezona: ', $this->model->getSeasons()->order('start_date DESC')->fetchPairs('id', 'name'))
			->setRequired();
	    
		$form->addSubmit('save', 'Uložit');
	    
		$form->onSuccess[] = callback($this, 'registerTeamSubmitted');
	    
		return $form;
	}
	
	
	
	/**
	 * Register team process
	 * @param Form $form 
	 */
	public function registerTeamSubmitted(Form $form)
	{
		$values = $form->values;
		
		$data = array (
		    'team_id' => $values->teamId,
		    'competition_id' => $values->competitionId,
		    'season_id' => $values->seasonId,
		);
		
		$this->model->getTeamsCompetitions()->insert($data);
		$this->flashMessage('Tým zaregistrován.', 'success');
		$this->redirect('Admin:teamRegister');
	}
	
	

}