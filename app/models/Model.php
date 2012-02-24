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

}