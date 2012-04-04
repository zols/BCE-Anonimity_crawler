<?php
class MenuElement {
	public $id;
	public $text;
	public $link;
	public $subMenu = array();
	
	public function __construct($id, $text, $link) {
		$this->id 		= $id;
		$this->text 	= $text;
		$this->link		= $link;
		$this->subMenu 	= null;
	}
	
	public function addSubMenu($subMenu) {
		$this->subMenu[] = $subMenu;
	}
}
?>