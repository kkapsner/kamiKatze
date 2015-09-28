<?php

/**
 * DBItemFieldDBDynamicItem definition file
 */

/**
 * Description of DBItemFieldDBDynamicItem
 *
 * @author kkapsner
 */
abstract class DBItemFieldDBDynamicItem extends DBItemFieldDBItem implements DBItemFieldGroupInterface{
	private static $classNames = array(
		"DBItemFieldDBDynamicItemOneToOne",
		"DBItemFieldDBDynamicItemOneToN",
		"DBItemFieldDBDynamicItemNToOne",
		"DBItemFieldDBDynamicItemNToN",
	);
	
	protected static function create(DBItemClassSpecifier $classSpecifier, $properties){
		$properties["correlation"] = self::unifyCorrelation(
			array_read_key("correlation", $properties, "1to1")
		);
		$className = self::$classNames[$properties["correlation"]];
		$item = new $className($properties["name"]);
		$item->adoptProperties($classSpecifier, $properties);
		return $item;
	}
}