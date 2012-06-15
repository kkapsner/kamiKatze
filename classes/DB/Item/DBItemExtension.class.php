<?php

/**
 * Description of DBItemExtension
 *
 * @author kkapsner
 */
abstract class DBItemExtension extends ViewableHTML{
	/**
	 *
	 * @param string $class The class to check.
	 * @return bool If the extension is valid for this DBItem $class.
	 */
	abstract function isValidClass($class);

	/**
	 *
	 * @return string The extensions name.
	 */
	abstract function getName();
}

?>
