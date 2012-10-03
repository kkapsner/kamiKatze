<?php
/**
 * EmailSMTPSocketException
 */

/**
 * Description of EmailSMTPException
 *
 * @author Korbinian Kapsner
 * @package Email\SMTP
 */
class EmailSMTPSocketException extends EmailSMTPException{
	/**
	 * Constructor of EmailSMTPException
	 *
	 * @param int $code
	 * @param Exception $previous
	 */
	public function __construct($code, $previous = null){
		parent::__construct(socket_strerror($code), $code, $previous);
	}

	/**
	 * Calls socket_last_error() to get the last socket error and wrap it in a EmailSMTPSocketException.
	 *
	 * @param ressource $socket
	 * @return EmailSMTPException Last error on the specified socket.
	 */
	public static function getLast($socket = null){
		return new self(socket_last_error($socket));
	}
}

?>
