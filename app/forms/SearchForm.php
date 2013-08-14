<?php

use Nette\Http\Request,
 Nette\Application\UI\Form;

/**
 * Description of CommentsForm
 *
 * @author Potty
 */
class SearchForm extends Nette\Application\UI\Form {
    
    public function __construct() 
    {
		parent::__construct();

		$this->addText('query')
			->setAttribute('placeholder', 'Hledat');

		$this->addSubmit('submit', 'Hledat');

		$this->onSuccess[] = callback($this, 'process');
    }
    


	public function process()
    {
		$values = $this->getValues();
		$this->presenter->redirect('Page:search', array('search' => $values->query));
		$this->presenter->terminate();
    }
    
}
