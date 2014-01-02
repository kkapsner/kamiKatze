<?php

/**
 * MarkdownParser definition file
 */

/**
 * MarkdownParser parses a string and returns a line parse structure
 *
 * @author kkapsner
 * @package Markdown
 */
class MarkdownParser{
	
	/**
	 * 
	 * @param type $str
	 * @return MarkdownLines[] the parsed line structure
	 */
	public static function parse($str){
		$lines = preg_split("/\\r\\n?|\\n/", $str);
		
		$ret = new MarkdownDocument();
		$lastLine = null;
		$emptyLineBetween = false;
		/*@var $lastLine MarkdownLine*/
		foreach ($lines as $line){
			if ($line === ""){
				$emptyLineBetween = true;
			}
			elseif (preg_match("/^(?:=+|\\-+)\\s*$/", $line)){
				if ($lastLine instanceof MarkdownLine){
					if (!$emptyLineBetween){
						if (!$lastLine->isHeading){
							$lastLine->headingLevel = $line{1} === "="? 1: 2;
						}
						$lastLine->isHeading = true;
					}
					else {
						$lineNode = new MarkdownSeparator($lastLine);
						$ret[] = $lineNode;
						$lastLine = $lineNode;
					}
				}
				$emptyLineBetween = false;
			}
			else {
				$lineNode = new MarkdownLine("", $lastLine);
				if (preg_match("/^(#+)\\s*(.*)$/", $line, $matches)){
					$lineNode->isHeading = true;
					$lineNode->headingLevel = strlen($matches[1]);
					$lineNode->line = $matches[2];
				}
				elseif (preg_match("/^\\s*[*+\\-]\\s*(.*)$/", $line, $matches)){
					$lineNode->isList = true;
					$lineNode->listType = MarkdownLine::LIST_TYPE_UNORDERED;
					$lineNode->line = $matches[1];
				}
				elseif (preg_match("/^\\s*\\d+\\.\\s*(.*)$/", $line, $matches)){
					$lineNode->isList = true;
					$lineNode->listType = MarkdownLine::LIST_TYPE_ORDERED_NUMBERS;
					$lineNode->line = $matches[1];
				}
				elseif (preg_match("/^\\s*\\w+\\)\\s*(.*)$/", $line, $matches)){
					$lineNode->isList = true;
					$lineNode->listType = MarkdownLine::LIST_TYPE_ORDERED_CHARACTERS;
					$lineNode->line = $matches[1];
				}
				else {
					if (substr($line, -2) === "  "){
						$lineNode->newLine = true;
					}
					$lineNode->line = trim($line);
				}
				
				$lineNode->newParagraph = $emptyLineBetween;
				
				$ret[] = $lineNode;
				$lastLine = $lineNode;
				$emptyLineBetween = false;
			}
		}
		return $ret;
	}
}

?>