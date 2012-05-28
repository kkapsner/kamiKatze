<?php

/**
 * Description of BBCodeParser
 *
 * @author kkapsner
 */
class BBCodeParser{

	/**
	 * Charset of the BB-code.
	 * @var string
	 */
	public $charset = "UTF-8";

	/**
	 *
	 * @var StringIterator
	 */
	protected $codeIterator;

	/**
	 *
	 * @var BBCodeTagRoot
	 */
	protected $root;

	/**
	 *
	 * @var BBCodeTag
	 */
	protected $currentNode;

	/**
	 *
	 * @var string
	 */
	protected $text;

	public function __construct(){
		$this->codeIterator = new StringIterator();
	}

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

	protected function insertText(){
		$this->currentNode->appendChild(new BBCodeTagText(array("text" => $this->text)));
		$this->text = "";
	}

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
	protected static $noKeyChars = "=]";
	protected function parseKey(){
		return $this->codeIterator->goToNext(self::$noKeyChars);
	}

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

	protected function parseParameter(){
		$start = $this->codeIterator->key();
		$parameter = array();
		do {
			$key = trim($this->parseKey());
			if ($key){
				$value = true;
				if ($this->codeIterator->valid() && $this->codeIterator->current() === "="){
					$this->codeIterator->next();
					$this->codeIterator->goToNextNot(" \t\n\r");
					$value = $this->parseValue();
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
