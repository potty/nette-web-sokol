<?php

use Nette\Application\UI\Form;

/**
 * Description of PlayerPresenter
 *
 * @author Potty
 */
class PlayerPresenter extends BasePresenter {

    private $player;
    
    public function renderDefault()
    {
	$this->template->goalkeepers = $this->model->getPlayers()->where('team.name', 'Věteřov')->where('position.name', 'brankář')->order('surname ASC', 'name ASC');
	$this->template->defenders = $this->model->getPlayers()->where('team.name', 'Věteřov')->where('position.name', 'obránce')->order('surname ASC', 'name ASC');
	$this->template->midfielders = $this->model->getPlayers()->where('team.name', 'Věteřov')->where('position.name', 'záložník')->order('surname ASC', 'name ASC');
	$this->template->forwards = $this->model->getPlayers()->where('team.name', 'Věteřov')->where('position.name', 'útočník')->order('surname ASC', 'name ASC');
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
    }

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

}