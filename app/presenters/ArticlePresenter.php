<?php

use Nette\Image;
use Nette\Application\UI\Form;

/**
 * Description of ArticlePresenter
 *
 * @author Potty
 */
class ArticlePresenter extends BasePresenter {

    private $article;
    
    public function actionDefault($id) 
    {
	$this->article = $this->model->getArticles()->find($id)->fetch();
	if ($this->article === FALSE) {
	    $this->setView('notFound');
	}
    }

    public function renderDefault() {
	$this->template->article = $this->article;
    }
    
    
    
    /**
     * Returns array of pairs 'id' => 'player_surname player_name' 
     * @return array 
     */
    private function fetchPairsMatches()
    {
	$array = array();
	$matches = $this->model->getMatches()->where('played = ?', true)->order('date DESC');
	foreach ($matches as $match) {
	    $array[$match->id] = '(' . $match->date . ') ' . $match->ref('team', 'home_id')->name . ' - ' . $match->ref('team', 'away_id')->name;
	}
	return $array;
    }
    
    
    
    protected function createComponentArticleAddForm()
    {
	$form = new Form();
	$form->addSelect('category_id', 'Kategorie:', $this->model->getCategories()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat kategorii.');
	$form->addText('title', 'Titulek:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat titulek.');
	$form->addTextArea('text', 'Text')
		->addRule(Form::FILLED, '');
	$form->addSelect('match_id', 'Zápas:', $this->fetchPairsMatches())
		->setPrompt('- žádný -');
	$form->addSubmit('create', 'Přidat');
	$form->onSuccess[] = callback($this, 'articleAddFormSubmitted');
	return $form;
    }
    
    public function articleAddFormSubmitted(Form $form)
    {
	$data = array(
	    'title' => $form->values->title,
	    'category_id' => $form->values->category_id,
	    'text' => $form->values->text,
	    'created' => new DateTime(),
	    'user_id' => $this->user->getId(),
	);
	if ($form->values->match_id != '') $data['match_id'] = $form->values->match_id;
	$this->model->getArticles()->insert($data);
	$this->flashMessage('Článek přidán.', 'success');
	$this->redirect('Homepage:');
    }

}