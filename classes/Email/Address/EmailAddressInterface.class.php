<?php
/**
 * EmailAddressInterface definition file
 */

/**
 * Interface for interaction with a Email address
 *
 * @author Korbinian Kapsner
 * @package Email\Address
 */
interface EmailAddressInterface{
	/**
	 * Checks if the address(es) are valid.
	 * @return bool
	 */
	public function isValid();

	/**
	 * Returns an representation that can be inserted in an email header.
	 * @param int $alreadyConsumedChars
	 */
	public function toHeaderEncoded($alreadyConsumedChars = 0);

	/**
	 * Magic function __toString
	 */
	public function __toString();
}

?>
