<?php

use Nette\Http\Request,
 Nette\Application\UI\Form;

/**
 * Description of CommentsForm
 *
 * @author Potty
 */
class CommentsForm extends Nette\Application\UI\Form {
    
    private $model;
    


	public function __construct($model)
    {
		parent::__construct();
		$this->model = $model;

		$this->addProtection('Vypršel časový limit, odešlete formulář znovu.');

		$this->addHidden('is_guest')
			->setDefaultValue(1);

		$this->addHidden('article_id');

		$this->addText('author', 'Jméno:')
			->setRequired('Je nutné vyplnit jméno.');

		$this->addTextArea('text', 'Komentář:')
			->setRequired('Je nutné vyplnit komentář.');

		$this->addAntispam();

		$this->addSubmit('send', 'Odeslat');

		$this->onSuccess[] = callback($this, 'process');
    }
    


	public function process()
    {
		$values = $this->getValues();

		$data = array(
			'author' => $values->author,
			'text' => $values->text,
			'created' => new DateTime(),
			'is_guest' => $values->is_guest,
			'ip_address' => Nette\Environment::getHttpRequest()->getRemoteAddress(),
			'article_id' => $values->article_id,
		);

		$id = $this->model->getComments()->insert($data);
		$this->presenter->flashMessage('Komentář přidán.', 'success');
		$this->presenter->redirect("this#comment-$id");
    }
    
}
