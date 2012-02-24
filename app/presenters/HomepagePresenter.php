<?php

/**
 * Homepage presenter.
 *
 * @author	Pavel Potáček
 * @copyright	Copyright (c) 2012 Pavel Potáček	
 */
class HomepagePresenter extends BasePresenter
{
    
    /**
     * Renders Homepages with all articles
     */
    public function renderDefault()
    {
	// create visual paginator control
	$vp = new VisualPaginator($this, 'vp');
	$paginator = $vp->getPaginator();
	$paginator->itemsPerPage = 10;
	$paginator->itemCount = count($this->model->getArticles());
	
	$this->template->articles = $this->model->getArticles()
		->order('created DESC')
		->limit($paginator->itemsPerPage, $paginator->offset);
	if ($this->isAjax())
	    $this->invalidateControl('articles');
    }
    
    /**
     * Creates Visual Paginator component
     * @param string $name
     * @return VisualPaginator 
     */
    protected function createComponentVp($name) 
    {
	$vp = new VisualPaginator($this, $name);
	return $vp;
    }

}