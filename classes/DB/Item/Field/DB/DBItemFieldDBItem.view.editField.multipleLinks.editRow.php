<li>
<?php
/* @var $this DBItemFieldDBItem */
/* @var $context string */
/* @var $args mixed[] Array of the postName, the DBItem the sub item!*/

$this->view(
	"editField.select",
	true,
	$args
);
?>
	<button type="button" onclick="this.parentNode.parentNode.removeChild(this.parentNode);">delete</button>
</li>