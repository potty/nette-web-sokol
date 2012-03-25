<?php

/**
 * Základní třída modelu.
 */
class Model extends Nette\Object
{
    /** @var Nette\Database\Connection */
    public $database;


    /**
     * @param Nette\Database\Connection $database
     */
    public function __construct(Nette\Database\Connection $database)
    {
        $this->database = $database;
    }
    
    /**
     * Získá tabulku clanku.
     * @return Nette\Database\Table\Selection
     */
    public function getArticles()
    {
	return $this->database->table('article');
    }
    
    /**
     * Získá tabulku kategorii.
     * @return Nette\Database\Table\Selection
     */
    public function getCategories()
    {
	return $this->database->table('category');
    }

    /**
     * Získá tabulku uživatelů.
     * @return Nette\Database\Table\Selection
     */
    public function getUsers()
    {
	return $this->database->table('user');
    }
    
    /**
     * Ziska tabulku prav
     * @return Nette\Database\Table\Selection
     */
    public function getRights()
    {
	return $this->database->table('right');
    }
    
    /**
     * Ziska tabulku hracu
     * @return Nette\Database\Table\Selection
     */
    public function getPlayers()
    {
	return $this->database->table('player');
    }
    
    public function getTeams()
    {
	return $this->database->table('team');
    }
    
    public function getPositions()
    {
	return $this->database->table('position');
    }
    
    public function getSeasons()
    {
	return $this->database->table('season');
    }
    
    public function getTrainings()
    {
	return $this->database->table('training');
    }
    
    public function getPlayersTrainings()
    {
	return $this->database->table('player_training');
    }
    
    public function getCompetitions()
    {
	return $this->database->table('competition');
    }
   
    public function getMatches()
    {
	return $this->database->table('match');
    }
    
    public function getPlayersMatches()
    {
	return $this->database->table('player_match');
    }
    
    public function getEventTypes()
    {
	return $this->database->table('event_type');
    }
   
    public function getEvents()
    {
	return $this->database->table('event');
    }
    
    public function getSubstitutions()
    {
	return $this->database->table('substitution');
    }
    
    public function getComments()
    {
	return $this->database->table('comment');
    }
    
    public function getImages()
    {
	return $this->database->table('image');
    }
    
    public function getRoles()
    {
	return $this->database->table('role');
    }
    
    /**
     * Checks if login is already used
     * @param Nette\Forms\IControl $control
     * @return boolean 
     */
    public static function isLoginAvailable(Nette\Forms\IControl $control)
    {
	return !(bool) Nette\Environment::getService('model')->getUsers()->where('login', $control->getValue())->count();
    }

}