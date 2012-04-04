<?php
class LanguageController extends AbstractController {
	function indexAction() {
	}
	
	function changeAction() {
		$params = Registry::get('params');

		Registry::get('translator')->setCurrentLanguage($params[0]);

		header("Location: ".BASE_URI);
		exit();
	}
}
?>