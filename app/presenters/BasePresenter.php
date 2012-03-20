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
	//$this->template->menu = $this->menu;
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
	$table = array();
	foreach ($teams as $team) {
	    $points = 0;
	    $total_matches = 0;
	    $matches = $this->model->getMatches()->where('competition.name = ? AND played = ? AND (home_id = ? OR away_id = ?)', 'IV. třída', 1, $team->id, $team->id);
	    foreach ($matches as $match) {
		$total_matches++;
		if ($match->home_id == $team->id) {
		    $isHome = true;
		} else {
		    $isHome = false;
		}
		if ((($match->score_home > $match->score_away) && $isHome) ||
			(($match->score_home < $match->score_away) && $isHome == false)) {
		    $points += 3;
		} else if ($match->score_home == $match->score_away) {
		    $points += 1;
		}
	    }
	    $table[] = array(
		'team' => $team->name,
		'matches' => $total_matches,
		'points' => $points,
	    );
	}
	$this->template->table = $this->sortTable($table);
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
    
}
