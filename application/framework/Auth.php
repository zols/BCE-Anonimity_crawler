<?php
class Auth {
	private static $instance = null;

	private function __construct() {
	}

	public static function getInstance($startSession = true, $sessionID = null) {
		if (self::$instance === null) {
			if (isset($sessionID)) {
				session_id($sessionID);
			}

			if ($startSession) {
				session_start();
			}
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function login($table, $where) {
		$db = Registry::get('db');
		$numRows = $db->select(DBPREFIX.$table, '*', $where);
		if ($numRows == 1) {
			$_SESSION['loggedIn'] = true;
			$result = $db->fetch();
			return $result;
		} else {
			return null;
		}
	}

	public static function save($key, $data) {
		$_SESSION[$key] = $data;
	}

	public static function load($key) {
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}

		return false;
	}

	public static function isLoggedIn() {
		if (isset($_SESSION['loggedIn'])) {
			if ($_SESSION['loggedIn'] === true) {
				return true;
			}
		}

		return false;
	}

	public static function logout() {
		$_SESSION['loggedIn'] = false;
		session_destroy();
	}
}
?>