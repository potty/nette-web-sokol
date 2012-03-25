<?php

/**
 * Description of AdminPresenter
 *
 * @author Potty
 */
class AdminPresenter extends BasePresenter {

    public function beforeRender()
    {
	parent::beforeRender();
	$this->template->robots = 'noindex, nofollow';
    }
    
    public function renderUsers()
    {
	$this->template->users = $this->model->getUsers()->order('login ASC');
    }
    
    public function renderDefault()
    {
	
    }
    
    public function handleChangeActivation($userId, $isActive)
    {
	if ($isActive) {
	    $this->model->getUsers()->find($userId)->update(array('is_active' => 0));
	} else {
	    $this->model->getUsers()->find($userId)->update(array('is_active' => 1));
	}
	if ($this->presenter->isAjax()) {
	    $this->invalidateControl();
	} else {
	    $this->presenter->redirect('this');
	}
    }

}