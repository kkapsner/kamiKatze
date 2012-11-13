<?php
/**
 * BBCodeParser definition file
 */

/**
 * Parses BBCode.
 *
 * @author Korbinian Kapsner
 * @package BB
 */
class BBCodeParser{

	/**
	 * Charset of the BB-code.
	 * @var string
	 */
	public $charset = "UTF-8";

	/**
	 * The iterator for the code.
	 * @var StringIterator
	 */
	protected $codeIterator;

	/**
	 * The root element of the document.
	 * @var BBCodeTagRoot
	 */
	protected $root;

	/**
	 * The current node in processing.
	 * @var BBCodeTag
	 */
	protected $currentNode;

	/**
	 * Buffer for non-BB text.
	 * @var string
	 */
	protected $text;

	/**
	 * Constructor for BBCodeParser
	 */
	public function __construct(){
		$this->codeIterator = new StringIterator();
	}

	/**
	 * Parses the BBCode in the provided string and returns the BBCode document tree.
	 * @param string $code the code to parse
	 * @return BBCodeTagRoot
	 */
	public function parse($code){
		$this->currentNode = $this->root = new BBCodeTagRoot();
		$this->root->parser = $this;
		$this->text = "";
		$this->codeIterator->init($code);
		foreach ($this->codeIterator as $char){
			switch ($char){
				case "\\":
					$this->codeIterator->next();
					if ($this->codeIterator->valid()){
						$this->text .= $this->codeIterator->current();
					}
					else {
						$this->text .= char;
					}
					break;
				case "[":
					$this->parseTag();
					$this->codeIterator->prev();
					break;
				default:
					$this->text .= $char;
			}
		}
		$this->insertText();
		return $this->root;
	}

	/**
	 * Inserts the text buffer in the tree and clears the buffer.
	 */
	protected function insertText(){
		$this->currentNode->appendChild(new BBCodeTagText(array("text" => $this->text)));
		$this->text = "";
	}

	/**
	 * Part of the parser that is invoced if the parses thinks the current position is a BBCode-Tag.
	 */
	protected function parseTag(){
		$this->codeIterator->next();
		$closing = $this->codeIterator->current() === "/";
		if ($closing){
			$this->codeIterator->next();
		}
		$tagName = $this->codeIterator->goToNext(" \t\n\r]\x00/");
		if ($closing){
			$p = $this->currentNode;
			$tn = mb_strToLower($tagName, $this->charset);
			while ($p !== NULL && $p->getTagName() !== $tn){
				$p = $p->getParent();
			}
			if ($p === NULL){
				$this->text .= "[/" . $tagName;
			}
			else {
				$this->insertText();
				$this->codeIterator->goToNext("]");
				if ($this->codeIterator->valid()){
					$this->codeIterator->next();
				}
				$this->currentNode = $p->getParent();
			}
		}
		else {
			if (BBCodeTag::tagExists($tagName) && $this->currentNode->canContain($tagName)){
				$this->insertText();
				$parameter = $this->parseParameter();
				$newNode = BBCodeTag::createTag($tagName, $parameter);
				$this->currentNode->appendChild($newNode);
				if (!array_key_exists("/", $parameter)){
					$this->currentNode = $newNode;
				}
				$this->codeIterator->goToNext("]");
				if ($this->codeIterator->valid()){
					$this->codeIterator->next();
				}
			}
			else {
				$this->text .= "[" . $tagName;
			}
		}
	}

	/**
	 * The chars not to be in a key.
	 * @var string
	 */
	protected static $noKeyChars = " \t\r\n\v=]";
	
	/**
	 * Reads the code from the current position as if it is the start of a key and returns the retrieved key.
	 * @return string the found key.
	 */
	protected function parseKey(){
		return $this->codeIterator->goToNext(self::$noKeyChars);
	}

	/**
	 * Reads the code from the current position as if it is the start of a value and returns the retrieved value.
	 * @return string the found value
	 */
	protected function parseValue(){
		switch ($this->codeIterator->current()){
			case "'":
				$this->codeIterator->next();
				return $this->codeIterator->goToNext("'", '\\');
			case '"':
				$this->codeIterator->next();
				return $this->codeIterator->goToNext('"', '\\');
			default;
				return $this->codeIterator->goToNext(" \t\n\r]");
		}
	}

	/**
	 * Reads the code from the current position as if it is in a tag at the beginning of the parameter lis
	 * @return array Array of the found parameter as key => value pairs.
	 */
	protected function parseParameter(){
		$start = $this->codeIterator->key();
		$parameter = array();
		do {
			$this->codeIterator->goToNextNot(" \t\n\r");
			$key = trim($this->parseKey());
			$this->codeIterator->goToNextNot(" \t\n\r");
			if ($key){
				$value = true;
				if ($this->codeIterator->valid() && $this->codeIterator->current() === "="){
					$this->codeIterator->next();
					$this->codeIterator->goToNextNot(" \t\n\r");
					$value = $this->parseValue();
					$this->codeIterator->goToNextNot(" \t\n\r");
				}
				$parameter[$key] = $value;
			}
			else {
				break;
			}
		} while (true);
		return $parameter;
	}

}

?>
