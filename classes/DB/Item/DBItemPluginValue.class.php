<?php
/**
 * DBItemPluginValue class definition
 */

/**
 * Special case of a plugin. This plugin provides an additional value (usually computed by the DBItems real values).
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
abstract class DBItemPluginValue extends DBItemPlugin{

	/**
	 * Returns the additional value.
	 * 
	 * @param DBItem $item
	 * @return mixed The calculated value of the plugin for $item
	 */
	abstract function getValue(DBItem $item);
}

?>
