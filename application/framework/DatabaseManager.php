<?php
class DatabaseManager {
	private $character_set;
	private $database_link;
	private $query_counter;
	private $query_result;
	private $insertId;
	
    public function __construct($character_set = 'utf8') {
		$this->character_set = $character_set;
		$this->query_counter = 0;
		$this->database_link = @mysql_connect(DBHOST, DBUSERNAME, DBPASSWORD);
		if ($this->database_link) {
			mysql_select_db(DBNAME, $this->database_link);
			mysql_query("SET NAMES ".$this->character_set, $this->database_link);
			mysql_query("SET CHARACTER SET ".$this->character_set, $this->database_link);
		} else {
			die("Cannot connect to database");
		}
	}
	
	public function __destruct() {
		mysql_close($this->database_link);
	}
	
	public function query($query_text, $returnRows = true) {
		$this->query_result = mysql_query($query_text, $this->database_link);
		$this->query_counter++;
		
		if ($returnRows) {
			if ((!(strpos($query_text, 'INSERT') === false)) || (!(strpos($query_text, 'SHOW') === false))) {
				if (!(strpos($query_text, 'INSERT') === false)) {
					$this->insertId = mysql_insert_id($this->database_link);
				}
				return mysql_num_rows($this->database_link);
			}
			return mysql_affected_rows($this->database_link);
		}
	}

	/**
	 * $db->select(array('table1', 'table2'), array('fieldname' => 'fieldvalue'),
	 * array('conditionname' => 'conditionvalue'), 'field ASC', 1);
	 */
	public function select($tables, $fields, $where=null, $order=null, $limit=null, $showQuery = false) {
		$query_text = "SELECT ";
		$query_text.= $this->parseParameter($fields, ',');
		$query_text.= " FROM ";
		$query_text.= $this->parseParameter($tables, ',', 'AS', true);
		if (isset($where)) {
			$query_text.= " WHERE ";
			$query_text.= $this->parseParameter($where, 'AND', '=', false, false);
		}
		
		if (isset($order)) {
			$query_text.= " ORDER BY ".$order;
		}
		
		if (isset($limit)) {
			$query_text.= " LIMIT ".$limit;
		}

		if ($showQuery) {
			echo $query_text;
		}
		$this->query_result = mysql_query($query_text, $this->database_link);
		$this->query_counter++;
		return mysql_num_rows($this->query_result);
	}
	
	private function parseParameter($input, $group_separator, $element_separator = null, $element_prefix = false) {
		$output = '';

		if (is_array($input)) {
			$is_first = true;
			foreach ($input as $key => $value) {
				if ($is_first) {
					$is_first = false;
				} else {
					$output.= " ".$group_separator." ";
				}

				$output.= ($element_prefix?'`'.DBNAME.'`.':'');	
				if (is_numeric($key)) {
					if (strpos($value, '.') === false) {
						$output.= $this->resolveStar($value);		
					} else {
						$output.= $value;
					}				
				} else {
					if (!(strpos($key, '.') === false)) {
						$output.= $key;		
					} else {
						$output.= '`'.$key.'`';
					}

					if (isset($element_separator)) {
						$output.= ' '.$element_separator.' ';

						if (is_numeric($value)) {
							$output.= $value;
						} else {
							/*if (!(strpos($value, '.') === false)) {
								$output.= $value;		
							} else {*/
								if ($element_prefix) {
									$output.= '`'.$value.'`';
								} else {
									$output.= '\''.$value.'\'';
								}
							//}
						}
					} else {
						$output.= '.'.$this->resolveStar($value);
					}
				}
			}
		} else {
			if ((!(strpos($input, '*') === false)) || (!(strpos($input, '.') === false)) || (!(strpos($input, '=') === false))) {
				$output.= $input; // table
			} else {
				$output.= '`'.$input.'`';
			}
		}
		return $output;
	}
	
	private function resolveStar($input) {
		return (($input == '*')?'*':'`'.$input.'`');
	}
	
	public function fetch() {
		return mysql_fetch_assoc($this->query_result);
	}
	
	/**
	 * $db->update('table', array('key' => 'value'), array('key' => 'value'), 1);
	 */
	public function update($table, $values, $where=null, $limit = 0) {
		$is_first = true;
		$query_text = "UPDATE `".DBNAME."`.`".$table."` SET ";
		
		foreach ($values as $key => $value) {
			if ($is_first) {
				$is_first = false;
			} else {
				$query_text.= ", ";		
			}

			$query_text.= "`".$key."` = ";

			if (is_numeric($value)) {
				$query_text.= $value;
			} else {
				$query_text.= "'".$value."'";
			}
		}

		if (isset($where)) {
			$query_text.= " WHERE ";
			$is_first = true;

			if (is_array($where)) {
				foreach ($where as $key => $value) {
    				if ($is_first) {
						$is_first = false;
					} else {
						$query_text.= " AND ";		
					}
					$query_text.= "`".$table."`.`".$key."` = ";
					if (is_numeric($value)) {
						$query_text.= $value;
					} else {
						$query_text.= "'".$value."'";
					}
				}
			} else {
				$query_text.= $where;
			}
		}

		if (($limit > 0) || (!is_numeric($limit))) {
			$query_text.= " LIMIT ".$limit;
		}

		mysql_query($query_text, $this->database_link);
		$this->query_counter++;
		return mysql_affected_rows($this->database_link);
	}
	
	/**
	 * $db->insert('table', array('key' => 'value'));
	 */
	public function insert($table, $values) {
		$is_first = true;
		$query_text = "INSERT INTO `".DBNAME."`.`".$table."` (";
		foreach ($values as $key => $value) {
		  	if ($is_first) {
				$is_first = false;
			} else {
				$query_text.= ", ";		
			}
			$query_text.= "`".$key."`";
		}
		$query_text.= ") VALUES (";
		$is_first = true;
		foreach ($values as $key => $value) {
			if ($is_first) {
				$is_first = false;
			} else {
				$query_text.= ", ";		
			}
			
			if (is_numeric($value)) {
				$query_text.= $value;
			} else {
				$query_text.= "'".$value."'";
			}
		}
		$query_text.= ")";

		mysql_query($query_text, $this->database_link);
		$this->insertId = mysql_insert_id($this->database_link);
		$this->query_counter++;
		return $this->insertId;
	}
	
	/**
	 * $db->delete('table', array('key' => 'value'), 1);
	 */
	public function delete($table, $where = null, $limit = 0) {
		$is_first = true;
		$query_text = "DELETE FROM `".DBNAME."`.`".$table."` WHERE ";
		
		if (isset($where)) {
			if (is_array($where)) {
				foreach ($where as $key => $value) {
    				if ($is_first) {
						$is_first = false;
					} else {
						$query_text.= " AND ";		
					}
					$query_text.= "`".$table."`.`".$key."` = ";
					if (is_numeric($value)) {
						$query_text.= $value;
					} else {
						$query_text.= "'".$value."'";
					}
				}
			} else {
				$query_text.= $where;
			}
		}
		
		if (($limit > 0) || (!is_numeric($limit))) {
			$query_text.= " LIMIT ".$limit;
		}

		mysql_query($query_text, $this->database_link);
		$this->query_counter++;
		return mysql_affected_rows($this->database_link);
	}
	
	public function lastInsertId() {
		return $this->insertId;
	}
	
	public function getTotalNumberofQueries() {
		return $this->query_counter;
	}
}
?>