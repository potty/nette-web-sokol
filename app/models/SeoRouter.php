<?php

/**
 * Description of SeoRouter
 *
 * @author Potty
 */
class SeoRouter {

    public static function getId($pattern)
    {
	preg_match('[\d{1,6}$]', $pattern, $matches);
	return $matches[0];
    }
    
    public static function getArticleTitle($id)
    {
	$db = Nette\Environment::getService('database');
	$result = $db->query('SELECT title FROM article WHERE id = ?', $id)->fetch();
	$title = $result['title'];
	return Nette\Utils\Strings::webalize($title).'-'.$id;
    }
    
    public static function getPlayerName($id)
    {
	$db = Nette\Environment::getService('database');
	$result = $db->query('SELECT name, surname FROM player WHERE id = ?', $id)->fetch();
	$title = $result['name'] . ' ' . $result['surname'];
	return Nette\Utils\Strings::webalize($title).'-'.$id;
    }
    
    public static function getTeamName($id)
    {
	$db = Nette\Environment::getService('database');
	$result = $db->query('SELECT name FROM team WHERE id = ?', $id)->fetch();
	$title = $result['name'];
	return Nette\Utils\Strings::webalize($title).'-'.$id;
    }
    
    public static function getMatchName($id)
    {
	$db = Nette\Environment::getService('database');
	
	$result = $db->query('
	    SELECT s.name AS season, t1.name AS home, t2.name AS away 
	    FROM `match` m
	    JOIN team t1 ON (t1.id = m.home_id)
	    JOIN team t2 ON (t2.id = m.away_id)
	    JOIN season s ON (s.id = m.season_id)
	    WHERE m.id = ?', $id)->fetch();
	
//	$result = $db->query('
//	    SELECT round
//	    FROM `match`
//	    WHERE id = ?
//	', $id)->fetch();
	
	$title = $result['season'] . ' ' . $result['home'] . ' ' . $result['away'];
	return Nette\Utils\Strings::webalize($title).'-'.$id;
    }
    
}
