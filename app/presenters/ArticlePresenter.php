<?php

use Nette\Image;
use Nette\Application\UI\Form;
use Vodacek\Forms\Controls\DateInput;

/**
 * Description of ArticlePresenter
 *
 * @author Potty
 */
class ArticlePresenter extends BasePresenter {

    private $article;
    
    public function beforeRender() {
	parent::beforeRender();
	if ($this->user->isLoggedIn()) {
	    $this->template->allowedEdit = $this->acl->isAllowed($this->user->identity->roles[0], 'article', 'edit') && $this->getUser()->isLoggedIn();
	} else {
	    $this->template->allowedEdit = FALSE;
	}
    }
    
    public function actionDefault($id) 
    {
	$this->article = $this->model->getArticles()->find($id)->fetch();
	if ($this->article === FALSE) {
	    $this->setView('notFound');
	}
	if ($this->article->category->name == 'Zápasy') {
	    $this->redirect('Match:single', $this->article->match_id);
	}
    }
    
    public function actionEdit($id)
    {
	$this->article = $this->model->getArticles()->find($id)->fetch();
	if ($this->article === FALSE) {
	    $this->setView('notFound');
	}
	$this['articleEditForm']->setDefaults($this->article);
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
		->setAttribute('class', 'ckeditor')
		->addRule(Form::FILLED, 'Je nutné zadat text.');
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
    
    protected function createComponentArticleEditForm()
    {
	$form = new Form();
	$form->addHidden('id');
	$form->addSelect('category_id', 'Kategorie:', $this->model->getCategories()->fetchPairs('id', 'name'))
		->addRule(Form::FILLED, 'Je nutné vybrat kategorii.');
	$form->addText('title', 'Titulek:', 40, 100)
		->addRule(Form::FILLED, 'Je nutné zadat titulek.');
	$form->addDate('created', 'Datum:', DateInput::TYPE_DATETIME)
                                ->setRequired('Uveďte datum.');
	$form->addTextArea('text', 'Text')
		->setAttribute('class', 'ckeditor')
		->addRule(Form::FILLED, 'Je nutné zadat text.');
	$form->addSelect('match_id', 'Zápas:', $this->fetchPairsMatches())
		->setPrompt('- žádný -');
	$form->addSubmit('save', 'Uložit');
	$form->onSuccess[] = callback($this, 'articleEditFormSubmitted');
	return $form;
    }
    
    public function articleEditFormSubmitted(Form $form)
    {
	$data = array(
	    'title' => $form->values->title,
	    'category_id' => $form->values->category_id,
	    'text' => $form->values->text,
	    'created' => $form->values->created,
	    'user_id' => $this->user->getId(),
	);
	if ($form->values->match_id != '') $data['match_id'] = $form->values->match_id;
	$this->model->getArticles()->find($form->values->id)->update($data);
	$this->flashMessage('Článek aktualizován.', 'success');
	$this->redirect('Homepage:');
    }
    
    protected function createComponentCommentsForm()
    {
	$form = new CommentsForm($this->model);
	if ($this->getUser()->isLoggedIn()) {
	    $form['author']->setDefaultValue($this->getUser()->getIdentity()->login);
	    $form['author']->setAttribute('readonly', 'readonly');
	    $form['is_guest']->setValue(0);
	}
	$form->onSuccess[] = callback($form, 'process');
	return $form;
    }
    
    protected function createComponentComments()
    {
	return new Comments($this->model, $this->article->id);
    }

}