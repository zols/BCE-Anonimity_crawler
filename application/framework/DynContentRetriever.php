<?php
class DynContentRetriever {
	public static function retrieveContent($dyn_tid) {
		$db = Registry::get('db');

		$db->select(array(DBPREFIX.'dyn' => 'dyn'), array('title', 'content', 'meta_keywords', 'meta_description'), array('dyn.dyn_tid' => $dyn_tid, 'dyn.lang_tid' => Registry::get('lang')));

		return $db->fetch();
	}

	public static function retrieveContentGroup($dyn_group_tid) {
		$db = Registry::get('db');
		$dyns = array();
		$dyn_group = array();

		$db->select(array(DBPREFIX.'dyn_group' => 'dyn_group'), array('dyn_tid'), array('dyn_group.dyn_group_tid' => $dyn_group_tid, 'dyn_group.lang_tid' => Registry::get('lang')));

		while ($row = $db->fetch()) {
			array_push($dyn_group, $row['dyn_tid']);
		}

		foreach ($dyn_group as $value) {
			$db->select(array(DBPREFIX.'dyn' => 'dyn'), array('title', 'content', 'meta_keywords', 'meta_description'), array('dyn.dyn_tid' => $value, 'dyn.lang_tid' => Registry::get('lang')));
			$row = $db->fetch();
			$dyns[$value] = $row;
		}

		return $dyns;
	}
}
?>