<?php

use Nette\Application\UI\Form;
use Vodacek\Forms\Controls\DateInput;

/**
 * Description of TrainingPresenter
 *
 * @author Potty
 */
class TrainingPresenter extends BasePresenter {

    private $training;
    private $id = NULL;
    private $season = NULL;
    
    public function beforeRender()
    {
	parent::beforeRender();
	$this->template->robots = 'noindex, nofollow';
    }
    
    /**
     * Renders list of all trainings
     */
    public function renderDefault($season = NULL) {	
	if ($season == NULL) {
		$selectedSeason = $this->currentSeason;
	} else {
		$selectedSeason = $season;
	}
	$this->season = $season;
	$this->template->trainings = $this->model->getTrainings()->where('season_id', $selectedSeason)->order('date DESC');
	$result = $this->model->getSeasons()->select('name')->where('id', $selectedSeason)->fetch();
	$this->template->currentSeason = $result['name'];
    }
    
    /**
     * Validates given parameter, then sets a proper template
     * @param int $id 
     */
    public function actionSingle($id)
    {
	$this->training = $this->model->getTrainings()->find($id)->fetch();
	if ($this->training === FALSE) {
	    $this->setView('notFound');
	}
	$this->id = $id;
    }
    
    /**
     * Renders single training
     */
    public function renderSingle()
    {
	$this->template->training = $this->training;
    }
    
    /**
     * Form to add new training
     * @return Form 
     */
    protected function createComponentTrainingAddForm()
    {
	$form = new Form();
	$form->addSelect('seasonId', 'Sezona:', $this->model->getSeasons()->order('name ASC')->fetchPairs('id', 'name'));
	$form['seasonId']->setDefaultValue($this->currentSeason);
	$form->addDate('date', 'Datum:', DateInput::TYPE_DATETIME)
                                ->setRequired('Uveďte datum.');
	$form->addText('title', 'Titulek:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat titulek.');
	$form->addText('description', 'Popis:', 40, 100);
	$form->addSubmit('create', 'Vytvořit');
	$form->onSuccess[] = callback($this, 'trainingAddFormSubmitted');
	return $form;
    }

    /**
     * Handles successfully submitted addTrainingForm
     * @param Form $form 
     */
    public function trainingAddFormSubmitted(Form $form)
    {
	$data = array(
	    'date' => $form->values->date,
	    'title' => $form->values->title,
	    'season_id' => $form->values->seasonId,
	);
	if ($form->values->description != '') $data['description'] = $form->values->description;
	$this->model->getTrainings()->insert($data);
	$this->flashMessage('Trénink přidán.', 'success');
	$this->redirect('this');
    }
    
    /**
     * Creates component PlayerList of Veterov players
     * @return PlayerList 
     */
    protected function createComponentPlayerList()
    {
	$players = $this->getPlayersByFilter('Věteřov');
	return new PlayerList($players, $this->id, $this->model);
    }
    
    
    
    /**
     * Season select form
     * @return SeasonForm 
     */
    protected function createComponentSeasonForm()
    {
	    $form = new SeasonForm($this->model);
	    if ($this->season == NULL) {
		    $form['seasonId']->setDefaultValue($this->currentSeason);
	    } else {
		    $form['seasonId']->setDefaultValue($this->season);
	    }
	    return $form;
    }
}