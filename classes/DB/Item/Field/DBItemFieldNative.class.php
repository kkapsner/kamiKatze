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
		$db = DB::getInstance();
		return $db->quote($this->name, DB::PARAM_IDENT) . " = " . $db->quote($value);
	}
}

?>