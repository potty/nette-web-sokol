<?php

use Nette\Application\UI\Form;

class ImagePresenter extends BasePresenter {
    
    public function beforeRender() {
	parent::beforeRender();
	$this->template->robots = 'noindex, nofollow';
    }

    public function renderDefault() {
	$this->template->images = $this->model->getImages();
    }
    
    protected function createComponentImageAddForm()
    {
	$form = new Form();
	
	$form->addText('description', 'Popis:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat popis.');
	
	$form->addText('path', 'Cesta:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat cestu.');;
	
	$form->addSubmit('create', 'Přidat');
	$form->onSuccess[] = callback($this, 'imageAddFormSubmitted');
	return $form;
    }
    
    public function imageAddFormSubmitted(Form $form)
    {
	$data = array(
	    'description' => $form->values->description,
	    'path' => $form->values->path,
	);
	$this->model->getImages()->insert($data);
	$this->flashMessage('Obrázek přidán.', 'success');
	$this->redirect('Image:');
    }
    
}