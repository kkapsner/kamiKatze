<?php

/**
 * MarkdownLine definition file
 */

/**
 * Representation of a parsed Markdown line
 *
 * @author kkapsner
 * @package Markdown
 */
class MarkdownLine extends MarkdownElement{
	/**
	 * the real line
	 * @var String 
	 */
	public $line = "";
	
	/**
	 * if the line starts a new paragraph
	 * @var bool
	 */
	public $newParagraph = false;
	
	/**
	 * if the line is a heading
	 * @var bool
	 */
	public $isHeading = false;
	
	/**
	 * level of the heading if the line is a heading
	 * @var int
	 */
	public $headingLevel = 0;
	
	/**
	 * if the line is a list item
	 * @var bool
	 */
	public $isList = false;
	
	/**
	 * type of the list if the line is a list item
	 * can take the following values:
	 *    MarkdownLine::LIST_TYPE_UNORDERED
	 *    MarkdownLine::LIST_TYPE_ORDERED_NUMBERS
	 *    MarkdownLine::LIST_TYPE_ORDERED_CHARACTERS
	 * @var int
	 */
	public $listType = 0;
	
	const LIST_TYPE_UNORDERED = 0;
	const LIST_TYPE_ORDERED_NUMBERS = 1;
	const LIST_TYPE_ORDERED_CHARACTERS = 2;
	
	/**
	 * level of the list if the line is list item
	 * @var int
	 */
	public $listLevel = 0;
	
	/**
	 * Constructor
	 * @param String $line The content of the line
	 * @param MarkdownLine $previousLine the previouse line
	 */
	public function __construct($line = "", $previousLine = null){
		parent::__construct($previousLine);
		$this->line = $line;
	}
	
	/**
	 * Checks if the two lines have the exact same list type
	 * @param MarkdownLine $line
	 * @return bool if the list type of the two lines matches
	 */
	public function hasSameListType(MarkdownLine $line){
		return $this->isList && $line->isList &&
			$this->listLevel === $line->listLevel &&
			$this->listType === $line->listType;
	}
}

?>