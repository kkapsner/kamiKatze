<?php
/* @var $this DBItemFieldLink */
/* @var $args DBItem */
$value = $args->{$this->name};
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
