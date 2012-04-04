<?php
require_once('Menu/MenuElement.php');

class Menu
{
	private $menuArray = array();

    public function __construct() {
		$db = Registry::get('db');
		$lang = Registry::get('lang');

		$db->query("SELECT `menu_text`.`menu_text_id`, `menu_text`.`menu_text`, `menu_text`.`link`, `menu`.`parent_id` FROM `".DBPREFIX."menu_text` AS `menu_text` LEFT JOIN `".DBPREFIX."menu` AS `menu` ON (`menu`.`menu_id` = `menu_text`.`menu_id`) WHERE `menu_text`.`lang_tid` = '".$lang."'");
	    while ($row = $db->fetch()) {
			$dummyElement = new MenuElement($row['menu_text_id'], $row['menu_text'], $row['link']);
			if ($row['parent_id'] == 0) {
				$this->menuArray[] = $dummyElement;
			} else {
				//$key = array_search($row['parent_id'], $this->menuArray);
				foreach ($this->menuArray as $key => $value) {
					if ($this->menuArray[$key]->id == $row['parent_id']) break;
				}
				$this->menuArray[$key]->addSubMenu($dummyElement);
			}
	    }
	}
	
	public function getMenuArray() {
		return $this->menuArray;
	}
}
?>