<?php
class Translator {
	private $currentLanguage = "";
	private $translation_file;
	private $currentTranslation;
	private $currentLocale;

	public function __construct($translation_file, $startSession = true) {
		$this->translation_file = $translation_file;

		if ($startSession) {
			session_start();
		}

		if (isset($_SESSION["currentLanguage"])) {
			$this->currentLanguage = $_SESSION["currentLanguage"];
			$this->currentLocale = $_SESSION["currentLocale"];
		} else {
			$this->getDefaultLanguageFromDB();

			$_SESSION["currentLanguage"] = $this->currentLanguage;
			$_SESSION["currentLocale"] = $this->currentLocale;
		}

		setlocale(LC_ALL, $this->currentLocale);
	}

	public function setCurrentLanguage($currentLanguage) {
		$availableLanguages = $this->getAvailableLanguages();

		if (array_key_exists($currentLanguage, $availableLanguages)) {
			$lang = $availableLanguages[$currentLanguage];

			$this->currentLanguage = $currentLanguage;
			$this->currentLocale = $lang["locale"];

			$_SESSION["currentLanguage"] = $currentLanguage;
			$_SESSION["currentLocale"] = $lang["locale"];

			setlocale(LC_ALL, $lang["locale"]);
		}
	}

	public function getCurrentLanguage() {
		return $this->currentLanguage;
	}

	public function getDefaultLanguageFromDB() {
		$db = Registry::get('db');

		$select = $db->select(DBPREFIX.'lang', array('lang_tid', 'locale'), array('default' => 1));
		$result = $db->fetch();

		$this->currentLocale = $result["locale"];
		$this->currentLanguage = $result["lang_tid"];
	}

	public function getAvailableLanguages() {
		$db = Registry::get('db');
		$arr = array();

		$select = $db->select(DBPREFIX.'lang', '*', null, '`'.DBPREFIX.'lang`.`default` DESC, `'.DBPREFIX.'lang`.`lang_name` ASC');
		while ($result = $db->fetch()) {
			$arr[$result['lang_tid']] = array('name' => $result['lang_name'], 'locale' => $result['locale']);
		}

		return $arr;
	}

	public function getCurrentTranslation() {
		$absolute_path = $_SERVER['DOCUMENT_ROOT'].'/'.LANGUAGE_PATH.'/'.$this->translation_file.'.'.$this->currentLanguage;

		if (file_exists($absolute_path)) {
			$this->currentTranslation = parse_ini_file($absolute_path, true);
		} else {
			Registry::get('logger')->writeLog($absolute_path.'.'.$this->currentLanguage." not found", 1);
			die("Language files can not be found");
		}

		return $this->currentTranslation;
	}

	public function translate($section, $entry) {
		if (!isset($this->currentTranslation[$section][$entry])) {
			Registry::get('logger')->writeLog("Translation ".$section.".".$entry." not found in ".LANGUAGE_PATH.'/'.$this->translation_file.'.'.$this->currentLanguage, 1);
			die("Translation ".$section.".".$entry." not found in ".LANGUAGE_PATH.'/'.$this->translation_file.'.'.$this->currentLanguage);
		}

		return $this->currentTranslation[$section][$entry];
	}
}
?>