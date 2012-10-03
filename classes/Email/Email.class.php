<?php
/**
 * Email definition file
 */

/**
 * Email generation class
 *
 * @author Korbinian Kapsner
 * @package Email
 */

class Email{
	/**
	 * New line constant
	 */
	const newLine = "\r\n";

	/**
	 * used charset
	 * @var string
	 */
	protected $charset = "utf-8";

	/**
	 * The FROM header
	 * @var EmailHeaderAddress
	 */
	private $from;
	
	/**
	 * The REPLY-TO header.
	 * @var EmailHeaderAddress
	 */
	public $replyTo;

	/**
	 * The TO header.
	 * @var EmailHeaderAddress
	 */
	private $to;

	/**
	 * The SUBJECT header.
	 * @var EmailHeader
	 */
	private $subject;

	/**
	 * Array of EmailHeaders
	 * @var array
	 */
	public $headers = array();

	/**
	 * The master part of the mail
	 * @var EmailPart
	 */
	protected $masterPart;

	/**
	 * The main text part
	 * @var EmailPart
	 */
	protected $textMasterPart;

	/**
	 * The text part
	 * @var EmailPart
	 */
	protected $textPart;

	/**
	 * The HTML part
	 * @var EmailPart
	 */
	protected $htmlPart = NULL;

	/**
	 * The part for inline attachments
	 * @var EmailPart
	 */
	protected $inlinePart = NULL;

	/**
	 * Constructor
	 * @param EmailAddress $to = ""
	 * @param string $subject = ""
	 * @param string $nachricht = ""
	 * @param string|EmailAddress|EmailAddressGroup $from = ""
	 */
	public function __construct(EmailAddress $to = NULL, $subject = "", $nachricht = "", EmailAddress $from = NULL){		
		$this->addHeader(new EmailHeaderMessageID());
		$this->addHeader(new EmailHeaderMIMEVersion());

		$this->from = new EmailHeaderAddress("From", $from);
		$this->addHeader($this->from);

		$this->replyTo = new EmailHeaderAddress("Reply-To");
		$this->addHeader($this->replyTo);

		$this->to = new EmailHeaderAddress("To", $to);
		$this->addHeader($this->to);

		$this->subject = new EmailHeader("Subject", $subject);
		$this->addHeader($this->subject);

		$this->textPart = new EmailPart(
			"text/plain",
			EmailEncoder::quotedPrintable($nachricht),
			$this->charset,
			"",
			array(new EmailHeader("Content-Transfer-Encoding", "quoted-printable"))
		);

		$this->masterPart = $this->textMasterPart = $this->textPart;
	}

	/**
	 * Sets the charset of the e-mail to a certain value. Validity is not checked.
	 * @param string $charset
	 */
	public function setCharset($charset){
		if (strToLowert($charset) == "latin1"){
			$charset = "iso-8859-1";
		}

		$this->textMasterPart->setCharset($charset);
		$this->charset = $charset;
	}
	
	/**
	 * Sets the plain text of the mail.
	 * @param type $str
	 */
	public function setText($str){
		$this->textPart->content = EmailEncoder::quotedPrintable($str);
	}
	
	/**
	 * Sets the HTML representation of the mail.
	 * @param type $html
	 */
	public function setHTML($html){
		$html = EmailEncoder::quotedPrintable($html);
		if ($this->htmlPart === NULL){
			$this->htmlPart = new EmailPart(
				"text/html",
				$html,
				$this->charset,
				"",
				array(new EmailHeader("Content-Transfer-Encoding", "quoted-printable"))
			);
			$this->textMasterPart = new EmailPart('multipart/alternative', '');
			
			if ($this->masterPart === $this->textPart){
				$this->masterPart = $this->textMasterPart;
			}
			else {
				$this->textPart->parentPart->replacePart($this->textMasterPart, $this->textPart);
			}

			$this->textMasterPart->addPart($this->textPart);
			$this->textMasterPart->addPart($this->htmlPart);
		}
		else {
			$this->htmlPart->content = $html;
		}
	}

	/**
	 * Adds a header to the mail. There is NO checking for validity (eg two from header, etc.). To check if a header exists call getHeaders.
	 * A header can also be inserted several times.
	 * @param EmailHeader $header 
	 */
	public function addHeader(EmailHeader $header){
		$this->headers[] = $header;
	}

	/**
	 * Removes a header from the mail. Only one occurance is deleted.
	 * @param EmailHeader $header
	 * @return bool if a header was removed.
	 */
	public function removeHeader(EmailHeader $header){
		foreach ($this->headers as $i => $h){
			if ($h === $header){
				array_splice($this->headers, $i, 1);
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns an array of the headers with the name equals $name (case insensitive) or all headers if $name is "" (empty string).
	 * @param string $name
	 * @return array
	 */
	public function getHeaders($name = ""){
		$name = strToUpper($name);
		$ret = array();
		foreach ($this->headers as $header){
			/* @var $header EmailHeader */
			if ($name === "" || strToUpper($header->getName()) === $name){
				$ret[] = $header;
			}
		}
		return $ret;
	}

	/**
	 * Adds an inline-attachment to the mail.
	 * @param type $type
	 * @param type $name
	 * @param type $data
	 * @return string Content-ID of the generated inline item (f.e. <img src="cid:$id">)
	 */
	public function addInline($type, $name, $data){
		if ($this->inlinePart === NULL){
			$this->inlinePart = new EmailPart("multipart/related", "");
			if ($this->textMasterPart->parentPart){
				$this->textMasterPart->parentPart->replacePart($this->inlinePart, $this->textMasterPart);
			}
			else {
				$this->masterPart = $this->inlinePart;
			}
			$this->inlinePart->addPart($this->textMasterPart);
		}
		$id = EmailEncoder::generateID() . "_byKKJS";

		$this->inlinePart->addPart(
			new EmailPart(
				$type,
				chunk_split(base64_encode($data), EmailEncoder::$maxLineLength, self::newLine),
				'',
				$name,
				array(
					new EmailHeader("Content-Transfer-Encoding", "base64"),
					new EmailHeaderNotEncodingValue("Content-ID", "<" . $id . ">"),
					new EmailHeaderParametric("Content-Disposition", "inline", array("filename" => $name))
				)
			)
		);

		return $id;
	}

	/**
	 * See addInline.
	 * @param type $type
	 * @param type $path
	 * @return string
	 * @return string cid
	 */
	public function addInlineFile($type, $path){
		if (!is_file($path)){
			throw new EmailException("Inline file '" . $path . "' not found.");
		}
		return $this->addInline($type, basename($path), file_get_contents($path));
	}

	/**
	 * Add an normal attachment to the mail.
	 * @param type $type
	 * @param type $name
	 * @param type $data
	 * @return EmailPart The added part.
	 */
	public function addAttachment($type, $name, $data){
		if ($this->masterPart === $this->textMasterPart || $this->masterPart === $this->inlinePart){
			$this->masterPart = new EmailPart("multipart/mixed", "This is a multi-part message in MIME format.");
			if ($this->inlinePart === NULL){
				$this->masterPart->addPart($this->textMasterPart);
			}
			else {
				$this->masterPart->addPart($this->inlinePart);
			}
		}
		
		$part = new EmailPart(
			$type,
			chunk_split(base64_encode($data), EmailEncoder::$maxLineLength, self::newLine),
			'',
			$name,
			array(
				new EmailHeader("Content-Transfer-Encoding", "base64"),
				new EmailHeaderParametric("Content-Disposition", "attachment", array("filename" => $name))
			)
		);
		$this->masterPart->addPart($part);
		return $part;
	}

	/**
	 * See addAttachment.
	 * @param type $type
	 * @param type $path
	 * @return EmailPart The added part.
	 */
	public function addAttachmentFile($type = "", $path = ""){
		if (!is_file($path)){
			throw new EmailException("Inline file '" . $path . "' not found.");
		}
		return $this->addAttachment($type, basename($path), file_get_contents($path));
	}

	/**
	 * Generates the mail head and returns it.
	 * @return string
	 */
	public function getHead(){
		$head = "";
		foreach ($this->headers as $h){
			$head .= $h;
		}
		$head .= $this->masterPart->getHead();
		return $head;
	}

	/**
	 * Generates the mail body and returns it.
	 * @return string
	 */
	public function getBody(){
		return $this->masterPart->getBody();
	}

	/**
	 * Sends the email. Either over $smtp or over mail().
	 * @param EmailSMTP $smtp
	 * @return bool If the mail was sent.
	 */
	public function send(EmailSMTP $smtp = NULL){
		
		if ($smtp === NULL){
			return mail(
				$this->to->getFoldedValue(),
				EmailEncoder::escapeHeaderValue($this->subject->getValue(), 9, $this->charset),
				$this->getBody(),
				$this->getHead(),
				""
			);
		}
		else {
			return $smtp->sendMail($this);
		}
	}

	/**
	 * Generates a the complete mail string.
	 * @return string
	 */
	public function __toString(){
		return $this->getHead() . self::newLine . $this->getBody();
	}
}

?>
