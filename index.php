<?php
header("Content-Type: text/html; charset=utf-8");
error_reporting(E_ALL|E_STRICT);

set_include_path(	'./application/configs' . PATH_SEPARATOR .
					'./application/framework' . PATH_SEPARATOR .
					'./application/site' . PATH_SEPARATOR .
					'./application/plugins' . PATH_SEPARATOR .
					get_include_path());

// Configs
require_once('settings.inc.php');
require_once('settings.front.inc.php');

// TimeZone
date_default_timezone_set(TIMEZONE);

// Framework libraries
require_once('Registry.php');
require_once('Logger.php');
require_once('Router.php');
require_once('DatabaseManager.php');
require_once('Translator.php');
require_once('URIParser.php');
//require_once('Menu.php');

// Smarty library
require_once('Smarty/Smarty.class.php');
require_once('smarty.front.inc.php');
$smarty->assign('baseUrl',	BASE_URL);
Registry::set('smarty', 	$smarty);

// Logger
//Registry::set('logger', new Logger('./work/log-frontend/log_'.date('Ymd').'.log', LOG_FILTER_LEVEL, LOG_RESOLVE_IP));

// Database
Registry::set('db', new DatabaseManager());

// Translator
$defaultTranslator = new Translator('main');

Registry::set('translator',	$defaultTranslator);
Registry::set('lang', 		$defaultTranslator->getCurrentLanguage());

// Site specific
//require_once('Site.php'); // Create abstract class

// URIParser
$defaultURIParser = new URIParser(BASE_URL);
switch ($defaultURIParser->URIoutput()) {
	case 200: // Proper controller or menu called
		Registry::set('controller', $defaultURIParser->getControllerName());
		Registry::set('action', 	$defaultURIParser->getActionName());
		Registry::set('template', 	$defaultURIParser->getTemplateName());
		Registry::set('params', 	$defaultURIParser->getParams());
		Registry::set('dynID', 		$defaultURIParser->getDynID());
		break;
	case 301: // IndexController
		Registry::set('controller', 'index');
		Registry::set('action', 	'index');
		Registry::set('template', 	'index');
		Registry::set('params', 	null);
		break;
	case 404: // Not found
		break;
}

// Router
if (Registry::isRegistered('controller')) {
	try {
		$router = new Router('./application/controllers');
		$router->loader(Registry::get('controller'), Registry::get('action'));
	} catch (Exception $e){
		echo "Controller error: ".$e;
	}
}

// Menu
//$defaultMenu = new Menu();
//Registry::get('smarty')->assign('menu', $defaultMenu->getMenuArray());

// Languages
//$smarty->assign('lang' , 	$defaultTranslator->getCurrentLanguage());
//$smarty->assign('langtext',	$defaultTranslator->getCurrentTranslation());

// Display
$smarty->display('_header.tpl');
if (Registry::isRegistered('template')) {
	$smarty->display(Registry::get('template').".tpl");
} else {
		$smarty->display("404.tpl");
}
$smarty->display('_footer.tpl');
?>