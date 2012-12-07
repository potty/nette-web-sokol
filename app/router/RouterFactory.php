<?php

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		
		$router[] = new Route('clanky/<id>', array(
	    	'presenter' => 'Article',
	    	'action' => 'default',
	    	'id' => array(
				Route::VALUE => 0,
				Route::FILTER_IN => callback('SeoRouter::getId'),
				Route::FILTER_OUT => callback('SeoRouter::getArticleTitle')
	    	)
		));
	
		$router[] = new Route('hrac/<id>', array(
	    	'presenter' => 'Player',
	    	'action' => 'single',
	    	'id' => array(
				Route::VALUE => 0,
				Route::FILTER_IN => callback('SeoRouter::getId'),
				Route::FILTER_OUT => callback('SeoRouter::getPlayerName')
	    	)
		));
	
		$router[] = new Route('kluby/<id>', array(
		    'presenter' => 'Team',
		    'action' => 'single',
		    'id' => array(
				Route::VALUE => 0,
				Route::FILTER_IN => callback('SeoRouter::getId'),
				Route::FILTER_OUT => callback('SeoRouter::getTeamName')
		    )
		));
	
		$router[] = new Route('zapas/<id>', array(
		    'presenter' => 'Match',
		    'action' => 'single',
		    'id' => array(
				Route::VALUE => 0,
				Route::FILTER_IN => callback('SeoRouter::getId'),
				Route::FILTER_OUT => callback('SeoRouter::getMatchName')
		    )
		));
	
		$router[] = new Route('soutez/zapasy', 'Match:competition');
		$router[] = new Route('soutez/tabulka', 'Match:table');
		
		$router[] = new Route('prihlaseni', 'Sign:in');
	
		$router[] = new Route('<presenter>/<action>[/<id>]', array(
		    'presenter' => array(
				Route::VALUE => 'Homepage',
				Route::FILTER_TABLE => array(
				    // retezec v url => presenter
				    'stranka' => 'Page',
				    'treninky' => 'Training',
				    'tymy' => 'Team',
				    'zapasy' => 'Match',
				    'hraci' => 'Player',
				    'kontakty' => 'Contact',
				)
		    ),
		    'action' => array(
				Route::VALUE => 'default',
				Route::FILTER_TABLE => array(
				    // retezec v url => view
				    'klub' => 'club',
				    'registrace' => 'register',
				    'detail' => 'single',
				    'hledani' => 'search',
				    'statistiky' => 'statistics'
				)
		    )
		));
		
		return $router;
	}

}
