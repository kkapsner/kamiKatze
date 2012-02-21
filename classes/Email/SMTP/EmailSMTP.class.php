<?php

/**
 * Description of EmailSMTP
 * 
 * @link http://tools.ietf.org/html/rfc5321
 * @author kkapsner
 */
class EmailSMTP{
	public $server;
	public $port;
	public $username;
	public $password;

	public $greeting;
	public $ehlo;

	/**
	 *
	 * @var ressource
	 */
	protected $connection = null;

	public function __construct($server, $port){
		$this->server = $server;
		$this->port = $port;
	}

	public function __destruct(){
		$this->close();
	}

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
	
	public function close(){
		$this->sendLine("QUIT", "221");
		fclose($this->connection);
		$this->connection = null;
	}

	public function authenticate($username, $password){
		$this->sendLine("AUTH LOGIN", "334 VXNlcm5hbWU6");
		$this->sendLine(base64_encode($username), "334 UGFzc3dvcmQ6");
		$this->sendLine(base64_encode($password), "235");
	}

	public function sendLine($line, $expectedAnswer = "250"){
		fwrite($this->connection, $line . "\r\n");
		$answer = $this->readAnswer();
		if (substr($answer, 0, strlen($expectedAnswer)) !== $expectedAnswer){
			throw new EmailSMTPException($answer);
			return false;
		}
		return $answer;
	}

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
