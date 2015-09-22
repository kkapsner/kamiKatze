<?php

/**
 *
 * @author kkapsner
 */
interface DBItemFieldInterface{
	public function translateNameToDB();
	public function translateToDB($value);
	
	public function translateRequestData($data, &$translatedData);
	
	public function appendDBNameAndValueForCreate($value, &$nameOut, &$valuesOut = null);
	public function appendDBNameAndValueForUpdate($value, &$propsOut);
	
	public function setValue(DBItem $item, $value);
	public function getValue(DBItem $item);
	
	public function validate($values);
	public function isValidValue($values);
}
