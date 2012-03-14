<?php
class Logger {
	// Log levels
	private $log_level = array('CRITICAL', 'ERROR', 'WARNING', 'INFORMATION', 'DEBUG');

	private $outputFilename;
	private $filterLevel;
	private $resolveIP;
	
    public function __construct($outputFilename, $filterLevel = 0, $resolveIP = true) {
		$this->outputFilename = $outputFilename;
		$this->filterLevel = $filterLevel;
		$this->resolveIP = $resolveIP;
	}
	
	public function writeLog($data, $level) {
		if ($this->filterLevel >= $level) {
			$output_data = date("[Y-m-d H:i:s]")." [".$_SERVER['REMOTE_ADDR'];
			if ($this->resolveIP) {
				$output_data.= "|".gethostbyaddr($_SERVER['REMOTE_ADDR']);
			}
			$output_data.= "] ".$this->log_level[$level].": ".$data."\n";
			file_put_contents($this->outputFilename, $output_data, FILE_APPEND);
		}
	}
}
?>