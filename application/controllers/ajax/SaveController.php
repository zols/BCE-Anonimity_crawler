<?php
require_once('FacebookConnect.php');

class SaveController extends AbstractController {
	function indexAction() {
		$db = Registry::get('db');

		if (isset($_POST['userId'])) {
			$userId = $_POST['userId'];
		} else {
			$userId = uniqid();
		}

		$data = array(
			'userId' => $userId,
			'datetime' => date("Y-m-d H:i:s"),
			'IP' => $_SERVER['REMOTE_ADDR'],
			//'host' => gethostbyaddr($_SERVER['REMOTE_ADDR']), // lelassítja a mentést
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
			'Flash' => (@isset($_POST['Flash'])?$_POST['Flash']:null),
			'Silverlight' => (@isset($_POST['Silverlight'])?$_POST['Silverlight']:null),
			'referrer' => $_POST['referrer'],
			'connectionType' => $_POST['connectionType'],
			'isGeo' => false,
			'positionLatitude' => $_POST['positionLatitude'],
			'positionLongitude' => $_POST['positionLongitude'],
			'fonts' => (@isset($_POST['fonts'])?$_POST['fonts']:null)
		);

		$id = $db->insert(DBPREFIX.'data', $data);
		
		// Facebook link
		$fb = new FacebookConnect();
		/*if ($fb->hasUserAccess()) {
			$auth_link = "";
		} else {
			$auth_link = $fb->getLoginUrl();
			//Registry::get('smarty')->assign('facebook_link', "<a href='".$fb->getLoginUrl()."' id='fbButton'>Facebook adataim megosztása</a>");
		}*/
		
		echo json_encode(array('ID' => $id,'userId' => $userId, 'link' => $fb->getLoginUrl($userId)));
	}

	function geoAction() {
		$db = Registry::get('db');
		$params = Registry::get('params');

		$data = array(
			'isGeo' => true,
			'positionLatitude' => $_POST['positionLatitude'],
			'positionLongitude' => $_POST['positionLongitude']
		);

		$db->update(DBPREFIX.'data', $data, array('id' => $params[0]), 1);

		echo 200;
	}

	function saveheavyfacebookdataAction() {
		$db = Registry::get('db');
		$params = Registry::get('params');
		$fb = new FacebookConnect();

		$data = array(
			'facebookFriends' => serialize($fb->retrieveFriends()),
			'facebookComments' => serialize($fb->retrievePosts()),
			'facebookLikes' => serialize($fb->retrieveLikes()),
			'facebookFeed' => serialize($fb->retrieveFeed())
		);
print_r($data);
		//$db->update(DBPREFIX.'data', $data, array('id' => $params[0]), 1);

		echo 200;
	}
}
?>