<?php
require_once('Router/AbstractController.php');

class Router {
	private $path;
	
    public function __construct($path) {
		if (is_dir($path) == false) {
            die('Invalid controller path: `'.$path.'`');
        }
        $this->path = $path;
	}
	
	public function loader($controller = 'index', $action = 'index') {
        $file = $this->path.'/'.ucfirst($controller).'Controller.php';

		if (is_readable($file) == false) {
			die ('`'.ucfirst($controller).'Controller` not found');
		}

		include $file;

        $class = ucfirst($controller).'Controller';
        $controllerClass = new $class();
		$action .= 'Action';

        if (is_callable(array($controllerClass, $action)) == true) {
			$controllerClass->$action();
		} else {
			die('`'.$class.'::'.$action.'` not found');
		}  
	}
}
?>