<?php
class MenuAdmin {
	const SITE_ADMIN	= 0;
	const SUPER_ADMIN	= 1;

	private $menuArray = array();

    public function __construct($type) {
		$db = Registry::get('db');
		switch ($type) {
			case self::SITE_ADMIN:
				$where = array('installed' => 1, 'active' => 1, 'public' => 1);
				break;
			default:
				$where = array('installed' => 1, 'active' => 1);			
				break;
		}
		
		$db->select(array(DBPREFIX.'admin_modul' => 'admin_modul'), array('admin_modul.modul_tid', 'admin_modul.modul_name', 'admin_modul.modul_description'), $where,  '`admin_modul`.`order` ASC');
		while ($row = $db->fetch()) {
			$this->menuArray[] = $row;
	    }
	}
	
	public function getMenuArray() {
		return $this->menuArray;
	}
}
?>