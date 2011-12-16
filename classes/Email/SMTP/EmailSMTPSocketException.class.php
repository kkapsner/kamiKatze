<?php

/**
 * Description of EmailSMTPException
 *
 * @author kkapsner
 */
class EmailSMTPSocketException extends EmailSMTPException{
	public function __construct($code, $previous = null){
		parent::__construct(socket_strerror($code), $code, $previous);
	}

	/**
	 * @param ressource $socket
	 * @return EmailSMTPException Last error on the specified socket.
	 */
	public static function getLast($socket = null){
		return new self(socket_last_error($socket));
	}
}

?>
