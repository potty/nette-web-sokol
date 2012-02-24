<?php

use Nette\Security as NS;


/**
 * Users authenticator.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{
	/** @var Nette\Database\Table\Selection */
	private $users;



	public function __construct(Nette\Database\Table\Selection $users)
	{
		$this->users = $users;
	}



	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->users->where('login', $username)->fetch();

		if (!$row) {
			throw new NS\AuthenticationException("Uživatel '$username' nebyl nalezen.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $this->calculateHash($password)) {
			throw new NS\AuthenticationException("Špatné heslo.", self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		$identity = new NS\Identity($row->id, $row->role->name);
		$identity->name = $row->name;
		return $identity;
	}



	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
	    return hash('sha1', $password);
	}

}
