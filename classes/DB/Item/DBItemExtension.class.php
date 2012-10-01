<?php
/**
 * DBItemExtension class definition.
 */

/**
 * Abstract class to be extended by a concrete extension.
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
abstract class DBItemExtension extends ViewableHTML{
	/**
	 * Checks if the extension is valid for a specific class.
	 * @param string $class The class to check.
	 * @return bool If the extension is valid for this DBItem $class.
	 */
	abstract function isValidClass($class);

	/**
	 * Getter for the extensions name.
	 * @return string The extensions name.
	 */
	abstract function getName();
}

?>
