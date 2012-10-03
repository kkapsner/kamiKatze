<?php
/**
 * EmailSMTP definition file
 */

/**
 * Class to connect and communicate to a SMTP server.
 * 
 * @link http://tools.ietf.org/html/rfc5321
 * @author Korbinian Kapsner
 * @package Email\SMTP
 * @todo implement more of the SMTP
 */
class EmailSMTP{
	/**
	 * The server address. For SSL connection prepend "ssl://" at the beginning
	 * @var string
	 */
	public $server;

	/**
	 * The server port.
	 * @var int
	 */
	public $port;

	/**
	 * The greeting received from the server.
	 * @var string
	 */
	public $greeting;

	/**
	 * The response form the server to the EHLO request
	 * @var string
	 */
	public $ehlo;

	/**
	 * The socket resource
	 * @var resource
	 */
	protected $connection = null;

	/**
	 * Constructor of EmailSMTP
	 *
	 * @param string $server The server address
	 * @param int $port The server port
	 */
	public function __construct($server, $port){
		$this->server = $server;
		$this->port = $port;
	}

	/**
	 * Destructor of EmailSMTP
	 */
	public function __destruct(){
		$this->close();
	}

	/**
	 * Connects to the server.
	 * @throws EmailSMTPException
	 */
	public function connect(){
		if ($this->connection === null){
			$this->connection = fsockopen($this->server, $this->port, $error, $errstr);
			if ($this->connection === false){
				throw new EmailSMTPException($errstr, $error);
			}
			else {
				$this->greeting = $this->readAnswer();
				preg_match('/^220 ([^ ]*)/', $this->greeting, $m);
				$this->ehlo = $this->sendLine("EHLO " . $m[1]);
			}
		}
	}

	/**
	 * Closes the connection to the server.
	 */
	public function close(){
		$this->sendLine("QUIT", "221");
		fclose($this->connection);
		$this->connection = null;
	}

	/**
	 * Performs an authentification on the server.
	 *
	 * @param type $username
	 * @param type $password
	 */
	public function authenticate($username, $password){
		$this->sendLine("AUTH LOGIN", "334 VXNlcm5hbWU6");
		$this->sendLine(base64_encode($username), "334 UGFzc3dvcmQ6");
		$this->sendLine(base64_encode($password), "235");
	}

	/**
	 * Sends one line to the server.
	 *
	 * @param type $line
	 * @param type $expectedAnswer
	 * @return string|false If the line was correct the answer is returned if not false is returned.
	 * @throws EmailSMTPException
	 */
	public function sendLine($line, $expectedAnswer = "250"){
		fwrite($this->connection, $line . "\r\n");
		$answer = $this->readAnswer();
		if (substr($answer, 0, strlen($expectedAnswer)) !== $expectedAnswer){
			throw new EmailSMTPException($answer);
			return false;
		}
		return $answer;
	}

	/**
	 * Sends a data package to the server.
	 * @param type $data
	 * @return type
	 */
	public function sendData($data){
		fwrite($this->connection, str_replace("\n.", "\n..", $data));
		return $this->sendLine("\r\n.");
	}
	
	/**
	 * sends RCPT TO lines to the address or to all members of the addressgroup.
	 * @param EmailAddressInterface $address 
	 */
	private function sendRCPT(EmailAddressInterface $address){
		if ($address instanceof EmailAddress){
			$this->sendLine("RCPT TO:<" . $address->address . ">");
		}
		if ($address instanceof EmailAddressGroup){
			foreach ($address as $member){
				$this->sendLine("RCPT TO:<" . $address->address . ">");
			}
		}
	}

	/**
	 * Sends a complete mail to the server.
	 *
	 * @param Email $mail
	 * @return string|false
	 */
	public function sendMail(Email $mail){
		$from = $mail->getHeaders("FROM");
		foreach ($from as $fromHeader){
			/* @var $fromHeader EmailHeaderAddress */
			foreach ($fromHeader->getValue() as $address){
				$this->sendLine("MAIL FROM:<" . $address->address . ">");
			}
		}
		$to = $mail->getHeaders("TO");
		foreach ($to as $toHeader){
			/* @var $toHeader EmailHeaderAddress */
			foreach ($toHeader->getValue() as $address){
				$this->sendRCPT($address);
			}
		}
		$cc = $mail->getHeaders("CC");
		foreach ($cc as $ccHeader){
			/* @var $ccHeader EmailHeaderAddress */
			foreach ($ccHeader->getValue() as $address){
				$this->sendRCPT($address);
			}
		}
		$bcc = $mail->getHeaders("BCC");
		foreach ($bcc as $bccHeader){
			/* @var $bccHeader EmailHeaderAddress */
			foreach ($bccHeader->getValue() as $address){
				$this->sendRCPT($address);
			}
			
		}
		$this->sendLine("DATA", "354");
		return $this->sendData($mail);
	}

	/**
	 * Reads the last answer from the socket.
	 *
	 * @return string
	 */
	public function readAnswer(){
		$answer = "";
		do {
			$part =  fgets($this->connection);
			preg_match('/^(\d{3})([ \-])(.+)$/', $part, $m);
			$answer .= $part;
		} while ($m && $m[2] === "-");
		#var_dump($answer);
		return $answer;
	}
}

?>
