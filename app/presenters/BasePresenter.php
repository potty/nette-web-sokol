<?php

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    protected $model;
    protected $currentSeason;
    protected $acl = null;
    
    /**
     * Seznam polozek menu
     * @var array
     */
//    public $menu = array (
//	'Domů' => 'Homepage:',
//	//'Homepage:' => 'Články',
//	'Soutěž' => array(
//	    'Zápasy' => 'Match:competition',
//	    'Tabulka' => 'Match:competition',
//	),
//	'Zápasy' => 'Match:',
//	'Sestava' => 'Player:',
//	'Statistiky' => 'Player:statistics',
//	'Tréninky' => 'Training:',
//    );
    
    public function startup() 
    {
	parent::startup();
	$this->model = $this->getService('model');
	$today = date('Y-m-d');
	$result = $this->model->getSeasons()->select('id')->where("start_date <= ? AND end_date >= ?", $today, $today)->fetch();
	$this->currentSeason = $result['id'];
	
	if (!$this->getUser()->isLoggedIn()) {
	    if ($this->getUser()->isInRole('guest')) {
		$this->acl = new AclModel();
		if (!$this->acl->isAllowed('guest', strtolower($this->name), $this->action)) {
		    $this->flashMessage('Do této části aplikace nemáte přístup. Byl jste přesměrován.', 'warning');
		    $this->redirect('Homepage:');
		}
	    }
	} else {
	    $this->acl = new AclModel();
	    $roles = $this->getUser()->getIdentity()->getRoles();
	    $role = array_shift($roles);

	    if (!$this->acl->isAllowed($role, strtolower($this->name), $this->action)) {
		$this->flashMessage('Do této části aplikace nemáte přístup. Byl jste přesměrován.', 'warning');
		$this->redirect('Homepage:');
	    }
	}
    }
    
    protected function createTemplate($class = NULL)
    {
	$template = parent::createTemplate($class);
	$template->registerHelper('age', function ($date) {
	    $timestamp = strtotime($date);
	    if ($timestamp == '') return '';
	    return floor((date("Ymd") - date("Ymd", $timestamp)) / 10000) . ' let';
	});
	return $template;
    }
    
    public function beforeRender()
    {
	parent::beforeRender();
	//$this->template->menu = $this->menu;
	LayoutHelpers::$thumbDirUri = 'images/thumbs';
	$this->template->registerHelper('thumb', 'LayoutHelpers::thumb');
	
	$this->template->lastMatch = $this->model->getMatches()
		->where('played = ? AND (home_id = ? OR away_id =?)', 1, 1, 1)
		->order('date DESC')
		->limit(1);
	$this->template->nextMatch = $this->model->getMatches()
		->where('played = ? AND (home_id = ? OR away_id =?)', 0, 1, 1)
		->order('date ASC')
		->limit(1);
	$this->template->scorers = $this->model->getEvents()
		->select('player.id AS id, player.name AS name, player.surname AS surname, COUNT(*) AS goals')
		->where('event_type.name', 'gól')
		->where('match.competition.name', 'IV. třída')
		->group('player_id')
		->order('goals DESC')
		->limit(4);
	
	$teams = $this->model->getTeams()->where('competition.name = ?', 'IV. třída');
	$this->template->table = $this->getCompetitionTable($teams, TRUE);
	if ($this->isAjax()) {
	    $this->invalidateControl('flash');
	}
    }
    
//    public function afterRender()
//    {
//	if (Nette\Diagnostics\Debugger::isEnabled())
//	    Nette\Diagnostics\Debugger::barDump($this->template->getParameters(), 'Template variables');
//    }
    
    /**
     * Sorts league table by points
     * @param array $table
     * @return array 
     */
    private function sortTable($table)
    {
	$points = array();
	foreach ($table as $item) {
	    $points[] = $item['points'];
	}
	array_multisort($points, SORT_DESC, $table);
	return $table;
    }
    
    public function handleSignOut()
    {
	$this->getUser()->logout();
	$this->redirect('Homepage:');
    }
    
    protected function isUserAllowedToAction($resource, $action)
    {
	$allowedAction = FALSE;
	if ($this->user->isLoggedIn()) {
	    $allowedAction = $this->acl->isAllowed($this->user->identity->roles[0], $resource, $action) && $this->user->isLoggedIn();
	}
	return $allowedAction;
    }
    
    /**
     * Return sorted competition table as array
     * @param Nette\Database\Table\Selection $teams
     * @param $simple Simple output selector
     * @return array
     */
    protected function getCompetitionTable(Nette\Database\Table\Selection $teams, $simple = FALSE)
    {
	$table = array();
	foreach ($teams as $team) {
	    $form = array();
	    $points = $played = $wins = $draws = $loses = $goals_for = $goals_against = $goal_diff = 0;
	    $matches = $this->model->getMatches()->where('competition.name = ? AND played = ? AND (home_id = ? OR away_id = ?)', 'IV. třída', 1, $team->id, $team->id)->order('date DESC');
	    foreach ($matches as $match) {
		$played++;
		$status = 'P';
		if ($match->home_id == $team->id) {
		    $isHome = true;
		    $goals_for += $match->score_home;
		    $goals_against += $match->score_away;
		} else {
		    $isHome = false;
		    $goals_for += $match->score_away;
		    $goals_against += $match->score_home;
		}
		if ((($match->score_home > $match->score_away) && $isHome) ||
			(($match->score_home < $match->score_away) && $isHome == false)) {
		    $points += 3;
		    $wins++;
		    $status = 'V';
		} else if ($match->score_home == $match->score_away) {
		    $points += 1;
		    $draws++;
		    $status = 'R';
		} else {
		    $loses++;
		}
		
		if ($played < 6) {
		    $form[] = array(
			'status' => $status,
			'match' => $match,
		    );
		}
	    }
	    $goal_diff = $goals_for - $goals_against;
	    
	    $data = array(
		'team' => $team->name,
		'team_id' => $team->id,
		'matches' => $played,
		'points' => $points,
	    );
	    if ($simple == FALSE) {
		$data['wins'] = $wins;
		$data['draws'] = $draws;
		$data['loses'] = $loses;
		$data['goals_for'] = $goals_for;
		$data['goals_against'] = $goals_against;
		$data['goal_diff'] = $goal_diff;
		$data['form'] = $form;
	    }
	    
	    $table[] = $data;
	}
	return $this->sortTable($table);
    }
    
    protected function createComponentSearchForm()
    {
	$form = new SearchForm();
	$form->onSuccess[] = callback($form, 'process');
	return $form;
    }
    
}
