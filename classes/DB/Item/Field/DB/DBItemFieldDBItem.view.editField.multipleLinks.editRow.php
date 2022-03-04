<li>
<?php
/** @var DBItemFieldDBItem $this */
/** @var string $context */
/** @var mixed[] $args Array of the postName, the DBItem the sub item!*/

$this->view(
	"editField.select",
	true,
	$args
);
?>
	<button type="button" onclick="this.parentNode.parentNode.removeChild(this.parentNode);">delete</button>
</li>