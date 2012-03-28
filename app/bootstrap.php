<?php

/**
 * My Application bootstrap file.
 */
use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\Route;


// Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';


// Enable Nette Debugger for error visualisation & logging
Debugger::$logDirectory = __DIR__ . '/../log';
Debugger::$strictMode = TRUE;
Debugger::enable();


// Configure application
$configurator = new Nette\Config\Configurator;
$configurator->setTempDirectory(__DIR__ . '/../temp');

// Enable RobotLoader - this will load all classes automatically
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

// Setup router
$router = $container->router;
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
	    'hraci' => 'Player'
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
//$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');


// Configure and run the application!
$application = $container->application;
//$application->catchExceptions = TRUE;
$application->errorPresenter = 'Error';
//DateInput register
Vodacek\Forms\Controls\DateInput::register();
$application->run();
