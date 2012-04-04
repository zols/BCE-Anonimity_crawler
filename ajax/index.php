<?php
// debug
/*$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;*/
// debug

header("Content-Type: text/html; charset=utf-8");
error_reporting(E_ALL|E_STRICT);

set_include_path(	'../application/configs' . PATH_SEPARATOR .
					'../application/framework' . PATH_SEPARATOR .
					'../application/site' . PATH_SEPARATOR .
					'../application/plugins' . PATH_SEPARATOR .
					get_include_path());

// Framework libraries
require_once('Registry.php');
require_once('Logger.php');
require_once('Router.php');
require_once('DatabaseManager.php');

// Configs
require_once('settings.inc.php');
require_once('settings.front.inc.php');

// TimeZone
date_default_timezone_set(TIMEZONE);

// Database
Registry::set('db', new DatabaseManager());

// Logger
Registry::set('logger', new Logger('../work/log-frontend-ajax/log_'.date('Ymd').'.log', LOG_FILTER_LEVEL, LOG_RESOLVE_IP));

// Explode URI
$uri = substr($_SERVER['REQUEST_URI'], strlen(BASE_URL."/ajax")+1);
if( substr($uri[strlen($uri)-1], -1) == '/') {
	$uri = substr($uri, 0, -1);
}

$uriArray = explode('/', $uri);

Registry::set('controller',	$uriArray[0]);
Registry::set('action',		$uriArray[1]);
Registry::set('params', 	array_slice($uriArray, 2));

// Router
if (Registry::isRegistered('controller')) {
	try {
		$router = new Router('../application/controllers/ajax');
		$router->loader(Registry::get('controller'), Registry::get('action'));
	} catch (Exception $e){
		echo "Controller error: ".$e;
	}
}

/*$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
echo "This page was created in ".$totaltime." seconds<br/>";
echo "Memory usage: ".number_format(memory_get_usage())." bytes<br/>";
echo "Peak memory usage: ".number_format(memory_get_peak_usage())." bytes<br/>";
echo "Total number of queries: ".$db->getTotalNumberofQueries()."<br/>";*/
?>