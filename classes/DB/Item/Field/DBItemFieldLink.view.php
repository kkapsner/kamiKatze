<?php
/** @var DBItemFieldLink $this */
/** @var DBItem $args */
$value = $this->getValue($args);
if ($value !== null){
	echo '<a href="' . $this->url($this->linkPrefix . $value . $this->linkPostfix) . '"';
	if ($this->externalLink){
		echo ' target="_blank"';
	}
	echo ">";
	$this->viewByName(get_parent_class($this), $context, true, $args);
	echo "</a>";
}
else {
	$this->viewByName(get_parent_class($this), $context, true, $args);
}
?>
