<?php

use Nette\Application\UI\Form;

/**
 * Form to select season
 *
 * @author Potty
 */
class SeasonForm extends Nette\Application\UI\Form {

	private $model;
	
	
	
	public function __construct($model)
	{
		parent::__construct();
		$this->model = $model;
		
		$this->addSelect('seasonId', 'Sezona:', $this->model->getSeasons()->order('name DESC')->fetchPairs('id', 'name'))
			->setAttribute("onchange", "submit()");
		
		$this->onSuccess[] = callback($this, 'process');
	}
	
	
	
	public function process()
	{
		$values = $this->getValues();
		$this->presenter->redirect('this', $values->seasonId);
	}
	
}
