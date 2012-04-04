<?php
require_once('FacebookConnect.php');

class FacebooksaveController extends AbstractController {
	function indexAction() {
		$db = Registry::get('db');
		$params = Registry::get('params');

		$fb = new FacebookConnect();
		$basic_data = $fb->retrieveBasicData();

		$data = array(
			'facebookId' => $basic_data["id"],
			'facebookName' => (@isset($basic_data["name"])?$basic_data["name"]:null),
			'facebookFirstname' => (@isset($basic_data["first_name"])?$basic_data["first_name"]:null),
			'facebookLastname' => (@isset($basic_data["last_name"])?$basic_data["last_name"]:null),
			'facebookGender' => (@isset($basic_data["gender"])?$basic_data["gender"]:null),
			'facebookUsername' => (@isset($basic_data["username"])?$basic_data["username"]:null),
			'facebookBirthday' => (@isset($basic_data["birthday"])?$basic_data["birthday"]:null),
			'facebookHometown' => (@isset($basic_data["hometown"])?$basic_data["hometown"]:null),
			'facebookLocation' => (@isset($basic_data["location"])?$basic_data["location"]:null),
		);

		$db->update(DBPREFIX.'data', $data, array('userId' => $params[0]));

		//exec();
	}
}
?>