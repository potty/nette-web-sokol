<?php

use Nette\Application\UI\Form,
	Nette\Mail\Message;

/**
 * Description of PagePresenter
 *
 * @author Potty
 */
class PagePresenter extends BasePresenter {

    private $searchResults;
    private $searchExpr;
    
    public function renderClub() {
	
    }
    
    public function actionSearch($search)
    {
	$this->searchResults = $this->model->getArticles()->where('title LIKE ? OR text LIKE ?', "%$search%", "%$search%")->order('created DESC');
	$this->searchExpr = $search;
    }
    
    public function renderSearch()
    {
	$this->template->searchResults = $this->searchResults;
	$this->template->searchExpr = $this->searchExpr;
    }
    
    protected function createComponentRegisterForm()
    {
	$form = new Form();
	
	$form->addText('name', 'Jméno:', 40, 100)
		->setRequired('Je nutné zadat jméno.');
	
	$form->addText('surname', 'Příjmení:', 40, 100)
		->setRequired('Je nutné zadat příjmení.');
	
	$form->addText('login', 'Login:', 40, 100)
		->setRequired('Je nutné zadat login.')
		->addRule(callback('Model', 'isLoginAvailable'), 'Tento login již existuje.');
	
	$form->addPassword('password', 'Heslo:', 40, 100)
		->setRequired('Je nutné zadat heslo.')
		->addRule(Form::MIN_LENGTH, 'Minimální délka hesla jsou %d znaky.', 4);
	
	$form->addPassword('passwordCheck', 'Ověření hesla:', 40, 100)
		->addRule(Form::EQUAL, 'Hesla nejsou shodná.', $form['password'])
		->setRequired('Je nutné znovu zadat heslo.');
	
	$form->addText('email', 'E-mail:', 40, 100)
		->setRequired('Vyplňte svůj e-mail.')
		->addRule(Form::EMAIL, 'Zadejte platný e-mail.');
	
	$form->addSubmit('save', 'Odeslat registraci');
	
	$form->onSuccess[] = callback($this, 'registerFormSubmitted');
	return $form;
    }
    
    public function registerFormSubmitted(Form $form)
    {
	$role = $this->model->getRoles()->where('name', 'member')->fetch();
	$values = $form->values;
	$data = array(
	    'name' => $values->name,
	    'surname' => $values->surname,
	    'login' => $values->login,
	    'password' => hash('sha1', $values->password),
	    'email' => $values->email,
	    'role_id' => $role->id,
	);
	$this->model->getUsers()->insert($data);
			
	// Send mail
	$body = 'Nový uživatel ' . $values->name . ' ' . $values->surname . ' se právě zaregistroval.';
	
	$mail = new Message;
	$mail->setFrom('sokol-veterov.cz <noreply@sokol-veterov.cz>')
		->addTo('admin@sokol-veterov.cz')
		->setSubject('Nová registrace')
		->setBody($body);
	try {
		$mail->send();
	} catch (\Exception $e) {
		throw $e;
	}
	
	$this->flashMessage('Registrace proběhla úspěšně. Váš účet nyní musí být aktivován adminem.', 'success');
	$this->redirect('Homepage:');
    }

}