<?php

class AclModel
{
    private $acl = null;
    
    public function __construct() 
    {
	$this->acl = new Nette\Security\Permission;
	
	$this->acl->addRole('guest');
	$this->acl->addRole('member', 'guest');
	$this->acl->addRole('admin');
	
	$this->acl->addResource('article');
	$this->acl->addResource('sign');
	$this->acl->addResource('homepage');
	$this->acl->addResource('player');
	$this->acl->addResource('training');
	$this->acl->addResource('match');
	$this->acl->addResource('page');
	$this->acl->addResource('team');
	$this->acl->addResource('image');
	$this->acl->addResource('admin');
	$this->acl->addResource('error');
	
	$this->acl->allow('guest', 
	    array(
		'sign', 
		'homepage', 
		'article', 
		'player', 
		'match', 
		'team'
	    ), 
	    array(
		'in', 
		'default', 
		'single', 
		'statistics', 
		'competition',
		'table',
	    )
	);
	$this->acl->allow('guest', array('page'), array('register', 'search', 'club'));
	
	$this->acl->allow('member', array('sign', 'training'), array('out', 'default', 'single'));
	
	$this->acl->allow('admin', Nette\Security\Permission::ALL, Nette\Security\Permission::ALL);
    }
    
    /**
     * Returns boolean if role has access to specific resource and action
     * @param type $role
     * @param type $resource
     * @param type $privilege
     * @return boolean
     */
    public function isAllowed($role, $resource, $privilege) 
    {
	return $this->acl->isAllowed($role, $resource, $privilege);
    }
}