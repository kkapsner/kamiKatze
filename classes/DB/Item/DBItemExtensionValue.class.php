<?php
/**
 * DBItemExtensionValue class definition
 */

/**
 * Special case of a extension. This extension provides an additional value (usually computed by the DBItems real values).
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
abstract class DBItemExtensionValue extends DBItemExtension{

	/**
	 * Returns the additional value.
	 * @param DBItem $item
	 * @return mixed The calculated value of the extension for $item
	 */
	abstract function getValue(DBItem $item);
}

?>
