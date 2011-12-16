<?php

/**
 *
 * @author kkapsner
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

	
	public function __toString();
}

?>
