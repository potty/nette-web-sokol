<?php

use Nette\Image;

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

}