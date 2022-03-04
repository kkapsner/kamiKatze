<?php
/** @var MarkdownLine $this*/

if ($this->isHeading){
	echo "<h" . ($this->headingLevel + MarkdownLine::$baseHeaderLevel) . ">" .
		$this->view("html.line", false, $args) . 
		"</h" . ($this->headingLevel + MarkdownLine::$baseHeaderLevel) . ">\n";
}
elseif ($this->isList){
	if (
		$this->newParagraph ||
		!$this->prev instanceof MarkdownLine ||
		!$this->hasSameListType($this->prev)
	){
		switch ($this->listType){
			case MarkdownLine::LIST_TYPE_UNORDERED:
				echo "<ul>\n";
				break;
			case MarkdownLine::LIST_TYPE_ORDERED_NUMBERS:
				echo '<ol type="1">' . "\n";
				break;
			case MarkdownLine::LIST_TYPE_ORDERED_CHARACTERS:
				echo '<ol type="a">' . "\n";
				break;
		}
	}
	echo "<li>" . $this->view("html.line", false, $args) . "</li>\n";
	
	if (
		!$this->next instanceof MarkdownLine ||
		$this->next->newParagraph ||
		!$this->hasSameListType($this->next)
	){
		switch ($this->listType){
			case MarkdownLine::LIST_TYPE_UNORDERED:
				echo "</ul>\n";
				break;
			case MarkdownLine::LIST_TYPE_ORDERED_NUMBERS:
			case MarkdownLine::LIST_TYPE_ORDERED_CHARACTERS:
				echo "</ol>\n";
				break;
		}
	}
}
else {
	if ($this->prev instanceof MarkdownLine){
		if (!$this->prev->isHeading){
			if ($this->newParagraph){
				echo "<p>\n";
			}
			elseif ($this->prev->newLine){
				echo "<br>\n";
			}
		}
	}
	echo $this->view("html.line", false, $args);
	echo "\n";
}
?>
