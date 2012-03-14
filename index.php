<?php
header("Content-Type: text/html; charset=utf-8");
error_reporting(E_ALL|E_STRICT);

set_include_path(	'./application/configs' . PATH_SEPARATOR .
					'./application/framework' . PATH_SEPARATOR .
					get_include_path());

// Configs
require_once('settings.inc.php');
require_once('settings.front.inc.php');

// TimeZone
date_default_timezone_set(TIMEZONE);
setlocale(LC_ALL, LOCALE);

// Framework libraries
//require_once('Registry.php');
//require_once('Logger.php');
require_once('DatabaseManager.php');

// Database
$db = new DatabaseManager();
//Registry::set('db', $db);

// Logger
//$logger = new Logger('./work/log-frontend/log_'.date('Ymd').'.log', LOG_FILTER_LEVEL, LOG_RESOLVE_IP);
//Registry::set('logger', $logger);

//Registry::get('logger')->writeLog("Teszt", 3);
switch ($_GET['do']) {
	case "save":
		if (isset($_POST['userId'])) {
			$userId = $_POST['userId'];
		} else {
			$userId = uniqid();
		}

		$data = array(
			'hasFbId' => (isset($_POST['hasFbId'])?$_POST['hasFbId']:false),
			'userId' => $userId,
			'facebookFirstName' => (isset($_POST['fbFirstName'])?$_POST['fbFirstName']:null),
			'facebookLastName' => (isset($_POST['fbLastName'])?$_POST['fbLastName']:null),
			'facebookGender' => (isset($_POST['fbGender'])?$_POST['fbGender']:null),
			'datetime' => date("Y-m-d H:i:s"),
			'IP' => $_SERVER['REMOTE_ADDR'],
			'host' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
			'screenWidth' => $_POST['screenWidth'],
			'screenHeight' => $_POST['screenHeight'],
			'httpUserAgent' => $_SERVER['HTTP_USER_AGENT'],
			'browser' => $_POST['browser'],
			'browserVersion' => $_POST['browserVersion'],
			'os' => $_POST['os'],
			'applicationCache' => ($_POST['applicationCache']=='true'?true:false),
			'history' => ($_POST['history']=='true'?true:false),
			'audio' => ($_POST['audio']=='true'?true:false),
			'video' => ($_POST['video']=='true'?true:false),
			'indexedDB' => ($_POST['indexedDB']=='true'?true:false),
			'localStorage' => ($_POST['localStorage']=='true'?true:false),
			'sessionStorage' => ($_POST['sessionStorage']=='true'?true:false),
			'webSockets' => ($_POST['webSockets']=='true'?true:false),
			'webSQLDatabase' => ($_POST['webSQLDatabase']=='true'?true:false),
			'webWorkers' => ($_POST['webWorkers']=='true'?true:false),
			'geoLocation' => ($_POST['geoLocation']=='true'?true:false),
			'touch' => ($_POST['touch']=='true'?true:false),
			'webGL' => ($_POST['webGL']=='true'?true:false),
			'Flash' => (isset($_POST['Flash'])?$_POST['Flash']:null),
			'Silverlight' => (isset($_POST['Silverlight'])?$_POST['Silverlight']:null),
			'referrer' => $_POST['referrer'],
			'connectionType' => $_POST['connectionType'],
			'isGeo' => false,
			'positionLatitude' => $_POST['positionLatitude'],
			'positionLongitude' => $_POST['positionLongitude'],
			'fonts' => (isset($_POST['fonts'])?$_POST['fonts']:null)
		);

		$db->insert(DBPREFIX.'data', $data);
		echo $userId;
		break;
	case "fbupdate":
		$data = array(
			'hasFbId' => true,
			'userId' => $_POST['facebookId'],
			'facebookFirstName' => (isset($_POST['fbFirstName'])?$_POST['fbFirstName']:null),
			'facebookLastName' => (isset($_POST['fbLastName'])?$_POST['fbLastName']:null),
			'facebookGender' => (isset($_POST['fbGender'])?$_POST['fbGender']:null)
		);
print_r($data);
		$db->update(DBPREFIX.'data', $data, array('userId' => $_GET['userId']));
		break;
	case "geoupdate":
		$data = array(
			'isGeo' => true,
			'positionLatitude' => $_POST['positionLatitude'],
			'positionLongitude' => $_POST['positionLongitude']
		);

		$db->update(DBPREFIX.'data', $data, array('userId' => $_GET['userId']), 1);
}
?>