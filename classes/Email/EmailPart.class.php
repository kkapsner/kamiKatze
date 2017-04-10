<?php
/**
 * EmailPart definition file
 */

/**
 * Representation of one part of an email
 *
 * @author Korbinian Kapsner
 * @package Email
 */

class EmailPart{
	/**
	 * The MIME content-type
	 * @var string
	 */
	public $contentType;

	/**
	 * The used charset.
	 * @var string
	 */
	public $charset;

	/**
	 * The parts name
	 * @var string
	 */
	public $name;

	/**
	 * The parts method
	 * @var string
	 */
	public $method;

	/**
	 * The content
	 * @var string
	 */
	public $content;

	/**
	 * The parent mail part
	 * @var EmailPart
	 */
	public $parentPart = NULL;

	/**
	 * Array of the child parts.
	 * @var EmailPart[]
	 */
	public $childParts = array();

	/**
	 * Array of EmailHeaders.
	 * @var EmailHeader[]
	 */
	public $headers = array();

	/**
	 * The boundary for multipart.
	 * @var string
	 */
	private $boundary = false;

	/**
	 * Constructor of EmailPart
	 *
	 * @param string $contentType
	 * @param string $content
	 * @param string $charset
	 * @param string $name
	 * @param EmailHeader[] $headers
	 */
	public function __construct($contentType, $content, $charset = '', $name = '', array $headers = NULL){
		if(!$this->boundary) $this->boundary = EmailEncoder::generateBoundary();

		$this->contentType = $contentType;
		$this->content = $content;
		$this->charset = $charset;
		$this->name = $name;
		if ($headers !== NULL){
			$this->headers = $headers;
		}
	}

	/**
	 * Sets the charset AND the charset of the childParts.
	 * @param type $charset
	 */
	public function setCharset($charset){
		$this->charset = $charset;
		foreach ($this->childParts as $part){
			$part->setCharset($charset);
		}
	}

	/**
	 * Adds a childPart to the part. If the new child was first in a different part it is removed there.
	 * @param EmailPart $part
	 * @return bool if the part was added.
	 */
	public function addPart(EmailPart $part){
		if ($part->parentPart){
			$part->parentPart->removePart($part);
		}

		$this->childParts[] = $part;
		$part->parentPart = $this;

		return true;
	}
	
	/**
	 * Removes a subpart from the part.
	 * @param EmailPart $part
	 * @return bool if the part was found and removed.
	 */
	public function removePart(EmailPart $part){
		$pos = array_search($part, $this->childParts);
		if ($pos !== false){
			array_splice($this->childParts, $pos, 1);
			$part->parentPart = NULL;
			return true;
		}
		return false;

	}

	/**
	 * Replaces a childpart by an other.
	 * @param EmailPart $replace
	 * @param EmailPart $needle
	 * @return bool if the needle was found and replaced.
	 */
	public function replacePart(EmailPart $replace, EmailPart $needle){
		$pos = array_search($needle, $this->childParts);
		if ($pos !== false){
			if ($replace->parentPart){
				$replace->parentPart->removePart($replace);
			}
			array_splice($this->childParts, $pos, 1, array($replace));
			$needle->parentPart = NULL;
			$replace->parentPart = $this;
			return true;
		}
		return false;
	}

	/**
	 * Generates the parts head and returns it.
	 * @return string The header of the mail part.
	 */
	function getHead(){
		$ct = new EmailHeaderContentType($this->contentType);
		if (count($this->childParts) !== 0){
			$ct->setParameter("boundary", $this->boundary);
		}
		if ($this->charset != ''){
			$ct->setParameter("charset", $this->charset);
		}
		if ($this->name != ''){
			$ct->setParameter("name", $this->name);
		}
		if ($this->method  != ''){
			$ct->setParameter("method", $this->method);
		}
		$str = $ct->__toString();
		foreach ($this->headers as $header){
			$str .= $header;
		}

		return $str;
	}

	/**
	 * Generates the parts body and returns it.
	 *
	 * @return string The body of the mail part.
	 */
	public function getBody(){
		$str = $this->content . Email::newLine;

		if (count($this->childParts) !== 0){
			foreach ($this->childParts as $subpart){
				$str .= "--" . $this->boundary . Email::newLine;
				$str .= $subpart;
			}
			$str .= "--" . $this->boundary . "--" . Email::newLine;
		}

		return $str;
	}

	/**
	 * Generates the complete part and returns it.
	 * 
	 * @return string Whole email part.
	 */
	function __toString(){
		return $this->getHead() .
			Email::newLine .
			$this->getBody();
	}
}

?>
