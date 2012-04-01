<?php
// General
define('BASE_URI',		"");
define('META_KEYWORDS',		"");
define('META_DESCRIPTION',	"");

// Log
define('LOG_FILTER_LEVEL', 4);			// 0-4 milyen üzeneteket loggoljon
define('LOG_RESOLVE_IP', true);			// feloldja-e az IP-ket

// Database
define("DBPREFIX",		"su_");			// database prefix
define("DBHOST", 		"localhost");	// database host
define("DBNAME", 		"userspyder");	// database name
define("DBUSERNAME",	"zbalogh");		// database username
define("DBPASSWORD",	"zolszols");	// database password

// Social
$FACEBOOK_CREDENTIALS = array(			// Facebook belépési kódok
	'api_key'    => '224552787576460',
	'api_secret' => 'a797c265bd8de69dc226f3aeae9d7105',
	'user_id'	 => 'balogh.zoltan'
);
/*$TWITTER_CREDENTIALS = array(			// Twitter belépési kódok
	'consumer_key'    => '',
	'consumer_secret' => '',
	'user_token'      => '',
	'user_secret'     => ''
);*/
?>