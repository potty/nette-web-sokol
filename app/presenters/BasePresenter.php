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
    public $menu = array (
	'Homepage:' => 'Domů',
	//'Homepage:' => 'Články',
	'Match:competition' => 'Soutěž',
	'Match:' => 'Zápasy',
	'Player:' => 'Sestava',
	'Player:statistics' => 'Statistiky',
	'Training:' => 'Tréninky',
    );
    
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
		    $this->flashMessage('Do této části aplikace nemáte přístup. Byl jste přesměrován.');
		    $this->redirect('Homepage:');
		}
	    }
	} else {
	    $this->acl = new AclModel();
	    $roles = $this->getUser()->getIdentity()->getRoles();
	    $role = array_shift($roles);

	    if (!$this->acl->isAllowed($role, strtolower($this->name), $this->action)) {
		$this->flashMessage('Do této části aplikace nemáte přístup. Byl jste přesměrován.');
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
	$this->template->menu = $this->menu;
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
	if ($this->isAjax()) {
	    $this->invalidateControl('flash');
	}
    }
    
    public function handleSignOut()
    {
	$this->getUser()->logout();
	$this->redirect('Homepage:');
    }
    
}
