<?php
/**
 * DBMySQLUtils definition file
 */

/**
 * Provides some MySQL utility functions
 *
 * @author kkapsner
 * @package DB
 */
class DBMySQLUtils{

	/**
	 * DBMySQLUtils constructor.
	 */
	private function __construct(){}

	/**
	 * Escapes a value.
	 *
	 * @param DB $db
	 * @param mixed $value
	 * @return string
	 *
	 * @assert ($this->db, NULL) === "NULL"
	 * @assert ($this->db, 1) === "1"
	 * @assert ($this->db, "NULL") === "'NULL'"
	 * @assert ($this->db, "'`\\") === "'\'`\\\\'"
	 */
	public static function escapeValue(DB $db, $value){
		if (is_null($value)){
			return "NULL";
		}
		elseif (is_int($value)){
			return strval($value);
		}
		else {
			return $db->quote($value);
		}
	}

	/**
	 * Renames a column in a table.
	 * @param DB $db
	 * @param type $table
	 * @param type $oldName
	 * @param type $newName
	 * @throws DBException
	 */
	public static function renameColumn(DB $db, $table, $oldName, $newName){
		$qTable = $db->quote($table, DB::PARAM_IDENT);
		$stmt = $db->prepare("SHOW FULL COLUMNS FROM " . $qTable . " WHERE `Field` = ?");
		if (!$stmt->execute(array($oldName))){
			throw new DBException("There is no column " . $oldName . " in Table " . $qTable . ".");
		}
		$fieldData = $stmt->fetch(DB::FETCH_ASSOC);
		$db->exec("ALTER TABLE " . $qTable . " CHANGE COLUMN " .
			$db->quote($oldName, DB::PARAM_IDENT) . " " .
			$db->quote($newName, DB::PARAM_IDENT) . " " .
			$fieldData["Type"] . " " .
			($fieldData["Collation"]? "COLLATE " . $db->quote($fieldData["Collation"]) . " ": "") .
			($fieldData["Null"] === "YES"? "": "NOT ") . "NULL " .
			"DEFAULT " . self::escapeValue($db, $fieldData["Default"]) . " " .
			((strpos($fieldData["Extra"], "auto_increment") !== false)? "AUTO_INCREMENT ": "") . " " .
			($fieldData["Comment"]? "COMMENT " . $db->quote($fieldData["Comment"]): "")
		);
	}

	/**
	 * Converts a Unix Epoch timestamp to a SQL DateTime string.
	 * @param int $date Unix Epoch timestamp
	 * @return string
	 */
	public static function date($date){
		return date("Y-m-d H:i:s", $date);
	}

	/**
	 * Validates a value according to a provided MySQL Data type.
	 * @param string $type
	 * @param mixed $value
	 * @return boolean
	 * @todo finish the implementation
	 */
	public static function validate($type, $value){
		$ltype = strtolower($type);
		if (preg_match('/^([a-z])+\s*(?:\((\d+)\))\s*(.*)$?/', $ltype, $m)){
			#Integer
			if (preg_match('/^(tiny|small|medium||big)int(?:eger)?$/', $m[1], $intM)){
				if (!ValidatorNumber::isIntLike($value)){
					return false;
				}
				$bits = ($intM === "tiny"? 1: ($intM === "small"? 2: ($intM === "medium"? 3: $intM === "big"? 8: 4)))* 8;
				if (strpos($m[2], "unsigned") === false){
					$bits--;
				}
				if ($value >= pow(2, $bits)) return false;
				return true;
			}
			
			if (preg_match('//', $ltype, $m)){

			}
		}
	}
	/**
	 * Checks if the provided string is a valid Time.
	 * @param string $time
	 * @param bool $oneDay if the time should be a time of a day.
	 * @return bool
	 *
	 * @assert ("100:10:20") === true
	 * @assert ("100:10:20", true) === false
	 * @assert ("4:3") === false
	 */
	public static function validTime($time, $oneDay = false){
		$parts = preg_split('/\D/', $time);
		if (count($parts) !== 3) return false;
		return ValidatorTime::isValidTime($parts[0], $parts[1], $parts[2], 0, $oneDay);
	}

	/**
	 * Chekcs if the provided string is a valid Date.
	 * @param string $date
	 * @return boolean
	 */
	public static function validDate($date){
		$parts = preg_split('/\D/', $date);
		if (count($parts) !== 3) return false;
		return ValidatorTime::isValidDate($parts[0], $parts[1], $parts[2]);
	}

	/**
	 * Checks if the provided string is a valid DateTime.
	 * @param string $dateTime
	 * @return boolean
	 */
	public static function validDateTime($dateTime){
		$parts = explode(" ", $dateTime);
		if (count($parts) !== 2) return false;
		return self::validDate($parts[0]) && self::validTime($parts[1], true);
	}
}

?>
