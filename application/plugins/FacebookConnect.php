<?php
require_once 'facebook/facebook.php';

class FacebookConnect {
	private $facebook;
	private $access_token;
	private $user;

	public function __construct() {
		global $FACEBOOK_CREDENTIALS;

		$this->facebook = new Facebook(array(
			'appId'  => $FACEBOOK_CREDENTIALS['api_key'],
			'secret' => $FACEBOOK_CREDENTIALS['api_secret'],
			'cookie' => true
		));

		$this->access_token = $this->facebook->getAccessToken();
	}

	public function hasUserAccess() {
		$this->user = $this->facebook->getUser();
		
		return $this->user;
	}

	public function getLoginUrl($userId) {
		return $this->facebook->getLoginUrl(
					array(
							'scope' => "read_stream, offline_access",
							//'redirect_uri' => "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]."?redirect=1"
							'redirect_uri' => "http://temp.sls.hu/temp/redirect/".$userId."/"
						)
					);
	}

	public function retrieveBasicData() {
		try {
			return $this->facebook->api('/me?fields=id,name,gender,birthday,hometown,location', 'GET', array('access_token' => $this->access_token));
		} catch (FacebookApiException $e) {
			die($e);
		}
	}

	public function retrieveFriends() {
		$output = array();
		$data = array("friends" => array("paging" => array("next" => 0)));
		$offset = 0;
		$limit = 5000;

		while (array_key_exists("next", $data["friends"]["paging"])) {
			$data = $this->facebook->api('/me?fields=friends&limit='.$limit.'&offset='.$offset, 'GET', array(
				'access_token' => $this->access_token
			));

			$output = array_merge($output, $data["friends"]["data"]);
			$offset += $limit;
		}

		return $output;
	}

	public function retrievePosts() {
		$output = array();// paging?
		$data = array("paging" => array("next" => 0));
		$offset = 0;
		$limit = 5;

		while (array_key_exists("paging", $data)) {
		//while (array_key_exists("next", $data["paging"])) {
			$data = $this->facebook->api('/me/posts?limit='.$limit.'&offset='.$offset, 'GET', array(
				'access_token' => $this->access_token
			));

			$output = array_merge($output, $data["data"]);
			$offset += $limit;
		}

		return $output;
	}

	public function retrieveLikes() {
		$output = array();
		$data = array("paging" => array("next" => 0));
		$offset = 0;
		$limit = 5000;

		while (array_key_exists("paging", $data)) {
			$data = $this->facebook->api('/me/likes?limit='.$limit.'&offset='.$offset, 'GET', array(
				'access_token' => $this->access_token
			));

			$output = array_merge($output, $data["data"]);
			$offset += $limit;
		}

		return $output;
	}

	public function retrieveFeed() {
		$output = array();
		$data = array("paging" => 0);
		$offset = 0;
		$limit = 50;

		while (array_key_exists("paging", $data)) {
			$data = $this->facebook->api('/me/feed?limit='.$limit.'&offset='.$offset, 'GET', array(
				'access_token' => $this->access_token
			));

			$output = array_merge($output, $data["data"]);
			$offset += $limit;
		}

		return $output;
	}

	/*
	regisztráció idõpontja - legelsõ post
	alapértelmezett megosztási jogosultság
	barátok listájának megtekintési jogosultsága
	*/
}
?>