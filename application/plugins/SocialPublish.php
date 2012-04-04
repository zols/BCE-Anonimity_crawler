<?php
class SocialPublish {
	// Application registration: https://dev.twitter.com/apps/new
	public static function publish2Twitter($credentials, $message) {
		require 'oAuth/tmhOAuth.php';

		$tmhOAuth = new tmhOAuth($credentials);

		$code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
			'status' => substr($message, 0, 140)
		));

		return $code;
	}

	// https://login.facebook.com/code_gen.php?api_k ey=API_KEY&v=1.0
	// http://www.facebook.com/login.php?api_key=API_KEY&connect_display=popup&v=1.0& next=http://www.facebook.com/connect/login_success.html&cancel_url=http://www.facebook.com/connect/login_failure.h tml&fbconnect=true&return_session=true&req_perms=read_stream,publish_stream,offline_access
	public static function publish2Facebook($credentials, $message, $link = null) {
		require_once 'facebook/facebook.php';

		$facebook = new Facebook(array(
			'appId'  => $credentials['api_key'],
			'secret' => $credentials['api_secret'],
			'cookie' => true
		));

		$access_token = $facebook->getAccessToken();

		try {
			if ($link != null) {
				$link = Registry::get('translator')->translate('news', 'more').": ".$link;
				$message = substr($message, 0, (420 - strlen($link) - 1))."\n".$link;
			} else {
				$message = substr($message, 0, 420);
			}			

			$publishStream = $facebook->api('/'.$credentials['user_id'].'/feed', 'POST', array(
				'access_token' => $access_token,
				'message' => $message
				)
			);

			/*if (!isset($publishStream['id'])) {
				print_r($publishStream);
			}*/

			return 200;
		} catch (FacebookApiException $e) {
			die($e);
		}

		return 404;
	}
}
?>