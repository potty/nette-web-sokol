<?php

use Nette\Application\UI;
use Nette\Database\Table\Selection;

class Comments extends UI\Control
{

	private $entityId;
	private $model;

	public function __construct(\Model $model, $entityId)
	{
		parent::__construct();
		$this->model = $model;
		$this->entityId = $entityId;
	}

	/**
	 * Renders Comments
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . '/Comments.latte');
		$this->template->comments = $this->model->getComments()->where('article_id', $this->entityId)->order('created ASC');
		$this->template->render();
	}

}
