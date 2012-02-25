<?php

use Nette\Application\UI\Form;
use Vodacek\Forms\Controls\DateInput;

/**
 * Description of MatchPresenter
 *
 * @author Potty
 */
class MatchPresenter extends BasePresenter {

    public function renderDefault() {
	$this->template->matches = $this->model->getMatches()->order('date ASC');
	$result = $this->model->getSeasons()->select('name')->where('id', $this->currentSeason)->fetch();
	$this->template->currentSeason = $result['name'];
    }
    
    protected function createComponentMatchAddForm()
    {
	$form = new Form();
	$form->addSelect('seasonId', 'Sezona:', $this->model->getSeasons()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat sezonu.');
	$form['seasonId']->setDefaultValue($this->currentSeason);
	$form->addSelect('competitionId', 'Soutěž:', $this->model->getCompetitions()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat soutěž.');
	$form->addText('round', 'Kolo:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat kolo.');
	$form->addDate('date', 'Datum:', DateInput::TYPE_DATETIME)
                                ->setRequired('Uveďte datum.');
	$form->addSelect('homeId', 'Domácí:', $this->model->getTeams()->order('name ASC')->fetchPairs('id', 'name'))
		->setPrompt('- Vyberte -')
		->addRule(Form::FILLED, 'Je nutné vybrat domácí tým.');
	$form->addSelect('awayId', 'Hosté:', $this->model->getTeams()->order('name ASC ')->fetchPairs('id', 'name'))
		->setPrompt('- Vyberte -')
		->addRule(Form::FILLED, 'Je nutné vybrat hostující tým.');
	$form->addSubmit('create', 'Vytvořit');
	$form->onSuccess[] = callback($this, 'matchAddFormSubmitted');
	return $form;
    }
    
    public function matchAddFormSubmitted(Form $form)
    {
	$data = array(
	    'competition_id' => $form->values->competitionId,
	    'date' => $form->values->date,
	    'season_id' => $form->values->seasonId,
	    'home_id' => $form->values->homeId,
	    'away_id' => $form->values->awayId,
	);
	if ($form->values->round != '') $data['round'] = $form->values->round;
	$this->model->getMatches()->insert($data);
	$this->flashMessage('Zápas přidán.', 'success');
	$this->redirect('this');
    }

}