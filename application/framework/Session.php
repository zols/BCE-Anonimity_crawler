<?php
class Session {
	private static $instance = null;

	private function __construct() {
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		session_start();
		return self::$instance;
	}

	public static function get($key) {
		return $_SESSION[$key];
	}

	public static function set($key, $value) {
		$_SESSION[$key] = $value;
	}
	
	public static function isRegistered($key) {
        return isset($_SESSION[$key]);
	}
	
	public static function destroy($key) {
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}
}
?>