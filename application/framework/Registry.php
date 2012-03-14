<?php
class Registry extends ArrayObject {
	private static $instance = null;

	public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS) {
		parent::__construct($array, $flags);
	}

	private static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function get($key) {
		if (!self::getInstance()->isRegistered($key)) {
			die('`'.$key.'` not found in registry');
		}
		return self::getInstance()->offsetGet($key);
	}

	public static function set($key, $value) {
		self::getInstance()->offsetSet($key, $value);
	}
	
	public static function delete($key) {
		self::getInstance()->offsetUnset($key);
	}

	public static function isRegistered($key) {
        return self::$instance->offsetExists($key);
	}
	
	public function offsetExists($key) {
        return array_key_exists($key, $this);
    }
}
?>