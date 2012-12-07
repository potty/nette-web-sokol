<?php

use Nette\Application\UI\Form;

/**
 * ContactPresenter
 *
 * @author Potty
 */
class ContactPresenter extends BasePresenter {
	
	/** @persistent int */
	public $id;
	
	/** @var object */
	private $contact;
	
	
	
	/**
	 * Edit contact
	 * @param int $id 
	 */
	public function actionEdit($id)
	{
		$this->contact = $this->model->getContacts()->find($id)->fetch();
		
		if ($this->contact === FALSE) {
			throw new Nette\Application\BadRequestException('Invalid parameter.', 404);
		}
		
		$this['contactForm']->setDefaults($this->contact);
	}
	
	
	
	/**
	 * Renders contacts list
	 */
	public function renderDefault()
	{
		$this->template->contacts = $this->model->getContacts()->order('player.surname ASC, player.name ASC');
	}
	
	
	
	/**
	 * Deletes selected contacts
	 * @param int $id 
	 */
	public function handleDelete($id)
	{
		$this->model->getContacts()->where('id', $id)->delete();
		$this->flashMessage('Contact deleted.', 'success');
		$this->redirect('this');
	}
	
	
	
	/**
	 * Form to manage contact
	 * @return Form 
	 */
	protected function createComponentContactForm()
	{
		$form = new Form();
		
		$form->addSelect('player_id', 'Hráč:', $this->fetchPairsPlayers())
			->addRule(Form::FILLED);
                
		$form->addText('phone', 'Telefon:', 40, 9)
			->setOption('description', 'Format: 9 digits without spaces')
			->addRule(Form::FILLED)
			->addRule(Form::PATTERN, 'Phone number must contain 9 digits without spaces.', '([0-9]\s*){9}');
		
		$form->addSubmit('create', 'Submit');
		
		$form->onSuccess[] = callback($this, 'processContactForm');
		
		return $form;
	}
	
	
	
	/**
	 * Contact process
	 * @param Form $form 
	 */
	public function processContactForm(Form $form)
	{
		if ($this->id && !$this->contact) { // checks contact existence in edit
			throw new BadRequestException;
		}
		
		$values = $form->getValues();
		$data = array(
			'player_id' => $values->player_id,
			'phone' => $values->phone
		);
		
		if ($this->id) { // edit contact
			$this->model->getContacts()->find($this->contact->id)->update($data);
			$this->flashMessage('Contact updated.', 'success');
			
		} else { // add contact
			$this->model->getContacts()->insert($data);
			$this->flashMessage('Contact created.', 'success');
		}
		$this->redirect('Contact:', array('id' => NULL));
	}

}