<?php

/**
 * Provides some MySQL utility functions
 *
 * @author kkapsner
 */
class DBMySQLUtils{
	private function __construct(){}

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

	public static function renameColumn(DB $db, $table, $oldName, $newName){
		$qTable = $db->quote($table, DB::PARAM_IDENT);
		$stmt = $db->prepare("SHOW FULL COLUMNS FROM " . $qTable . " WHERE `Field` = ?");
		$stmt->execute(array($oldName));
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

	public static function date($date){
		return date("Y-m-d H:i:s", $date);
	}
}

?>
