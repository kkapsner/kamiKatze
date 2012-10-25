<?php
/**
 * DBItemPlugin class definition.
 */

/**
 * Abstract class to be extended by a concrete plugin.
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
abstract class DBItemPlugin extends ViewableHTML{
	/**
	 * Checks if the plugin is valid for a specific class.
	 * @param string|DBItemClassSpecifier $classSpecifier The class to check.
	 * @return bool If the plugin is valid for this DBItem $class.
	 */
	abstract function isValidClass($classSpecifier);

	/**
	 * Getter for the plugins name.
	 * @return string The plugins name.
	 */
	abstract function getName();
}

?>
