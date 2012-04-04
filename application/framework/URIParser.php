<?php
class URIParser {
	private $uriArray;
	private $currentControllerName;
	private $currentTemplateName;
	private $currentActionName;
	private $currentParams;
	private $dynID;
	private $errorMessage = 301;
	
    public function __construct($baseURL) {
		// Parse URI
		$uri = substr($_SERVER['REQUEST_URI'], strlen($baseURL)+1);
		if( substr($uri[strlen($uri)-1], -1) == '/') {
			$uri = substr($uri, 0, -1);
		}
	
		$uri = preg_replace('/\./i', '', $uri);
		$this->uriArray = explode('/', $uri);

		if ($this->isValidModule() || $this->getControllerFromMenuNode()) {
			$this->errorMessage = 200;
		} else {
			if ((count($this->uriArray) == 1) && ($this->uriArray[0]=="")) {
				$this->errorMessage = 301;
			} else {
				$this->errorMessage = 404;
			}
		}
	}
	
	private function isValidModule() {
		$db = Registry::get('db');
		try {
			$db->query("SELECT modul.*, model.* FROM ".DBPREFIX."modul AS modul, ".DBPREFIX."model AS model WHERE modul.modul_link = '".$this->uriArray[0]."' AND model.model_id = modul.model_id");

			$result = $db->fetch();
			if (!empty($result)){
					$outcome = true;
					$this->currentControllerName = $result["controller"];
					$this->currentTemplateName = $result["template"];
					$this->currentActionName = $result["action"];					
					$this->dynID = $result["dyn_id"];
			} else {
				return false;
			}
		} catch (Exception $e){
			echo "URIParser/Checking module in DB: ".$e;
		}
		
		if (count($this->uriArray) > 1) {
			$this->currentParams = array_slice($this->uriArray, 1);
		}
		return true;		
	}
	
	private function getControllerFromMenuNode() {
		$db = Registry::get('db');
		$parent_node = 0;
		$outcome = false;
		
		for ($i = 0; $i<count($this->uriArray); $i++) {
			try {
				$db->query("SELECT menu_text.*, menu.*, model.* FROM ".DBPREFIX."menu_text AS menu_text, ".DBPREFIX."menu AS menu, ".DBPREFIX."model AS model WHERE menu.menu_id = menu_text.menu_id AND menu.model_id = model.model_id AND menu.parent_id = 0 AND menu_text.link = '".$this->uriArray[$i]."' AND menu_text.lang_tid = '".Registry::get('lang')."'");
				$result = $db->fetch();

				if (!empty($result)) {
					$outcome = true;
					$parent_node = $result["menu_id"];
					$this->currentControllerName = $result["controller"];
					$this->currentTemplateName = $result["template"];
					$this->currentActionName = $result["action"];
					$this->dynID = $result["content_id"];
				} else {
					if (count($this->uriArray) > $i) {
						$this->currentParams = array_slice($this->uriArray, $i);
					}
					break;
				}
			} catch (Exception $e){
				echo "URIParser/Checking module in DB: ".$e;
			}
		}		
		return $outcome;
	}

	public function getURIArray() {
		return $this->uriArray;
	}
	
	public function getControllerName() {
		return $this->currentControllerName;
	}

	public function getTemplateName() {
		return $this->currentTemplateName;
	}	

	public function getActionName() {
		return $this->currentActionName;
	}
	
	public function getParams() {
		return (isset($this->currentParams) ? $this->currentParams : null);
	}
	
	public function getDynID() {
		return (isset($this->dynID) ? $this->dynID : null);
	}
	
	public function URIoutput() {
		return $this->errorMessage;
	}
}
?>