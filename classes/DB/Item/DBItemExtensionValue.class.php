<?php

/**
 * Description of DBItemExtension
 *
 * @author kkapsner
 */
abstract class DBItemExtensionValue extends DBItemExtension{

	/**
	 *
	 * @param DBItem $item
	 * @return mixed The calculated value of the extension for $item
	 */
	abstract function getValue(DBItem $item);
}

?>
