<?php
abstract class AbstractController {
	function __construct() {
    }

	abstract function indexAction();
	
	function _redirect($url) {
		header('Location: '.$url);
	}
}
?>