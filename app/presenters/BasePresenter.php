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
    private $acl = null;
    
    /**
     * Seznam polozek menu
     * @var array
     */
    public $menu = array (
	'Homepage:' => 'Domů',
	//'Homepage:' => 'Články',
	'Match:' => 'Zápasy',
	'Player:' => 'Sestava',
	//'Stats:' => 'Statistiky',
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
		$acl = new AclModel();
		if (!$acl->isAllowed('guest', strtolower($this->name), $this->action)) {
		    $this->flashMessage('Do této části aplikace nemáte přístup. Byl jste přesměrován.');
		    $this->redirect('Homepage:');
		}
	    }
	} else {
	    $acl = new AclModel();
	    $roles = $this->getUser()->getIdentity()->getRoles();
	    $role = array_shift($roles);

	    if (!$acl->isAllowed($role, strtolower($this->name), $this->action)) {
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
	if ($this->isAjax()) {
	    $this->invalidateControl('flashMessages');
	}
    }
    
    public function handleSignOut()
    {
	$this->getUser()->logout();
	$this->redirect('Homepage:');
    }
    
}
