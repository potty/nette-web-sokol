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
	
	
	
	public function renderTeamRegister()
	{
		$this->template->registrations = $this->model->getTeamsCompetitions()->order('season.start_date DESC, team.name ASC');
	}
	
	
	
	public function renderPlayerRegister()
	{
		$this->template->registrations = $this->model->getTeamsPlayers()->order('season.start_date DESC, player.surname ASC, player.name ASC');
	}
	
	
	
	/**
	 * Activate/deactivate user
	 * @param type $userId
	 * @param type $isActive 
	 */
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