<?php

/**
 * DBItemFieldNative definition file
 */

/**
 * Parent class for all native fields
 *
 * @author kkapsner
 */
class DBItemFieldNative extends DBItemField implements DBItemFieldSearchable{
	/**
	 * returns a where-clause to match the desired value
	 * @param mixed $value the value to match
	 * @return string the where clause string
	 */
	public function getWhere($value){
		$db = $this->getDB();
		if ($this->null && $value === null){
			return $db->quote($this->name, DB::PARAM_IDENT) . " IS NULL";
		}
		else {
			if ($value instanceof DBItem){
				$value = $value->DBid;
			}
			return $db->quote($this->name, DB::PARAM_IDENT) . " = " . $db->quote($value);
		}
	}
}

?>
