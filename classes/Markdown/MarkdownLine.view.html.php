<?php
/*@var $this MarkdownLine*/

if ($this->isHeading){
	echo "<h" . $this->headingLevel . ">" .
		$this->html($this->line) . 
		"</h" . $this->headingLevel . ">\n";
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
	echo "<li>" . $this->html($this->line) . "</li>\n";
	
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
			elseif (!$this->prev->isList){
				echo "<br>\n";
			}
		}
	}
	echo $this->html($this->line);
	echo "\n";
}
?>
