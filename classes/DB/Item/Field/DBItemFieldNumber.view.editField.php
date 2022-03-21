<?php
/** @var DBItemFieldEnum $this */
/** @var string $context */
/** @var DBItem $args */

$attributes = 'name="' . $this->getPostName($args) . '"';
$value = $this->getValue($args);
if ($value !== null){
	$attributes .= ' value="' . $this->html($value) . '"';
}
foreach (array("step", "min", "max") as $attributeName){
	if ($this->{$attributeName}){
		$attributes .= ' ' . $attributeName . '="' . $this->{$attributeName} . '"';
	}
	
}

echo "\n\t\t\t" . '<input type="number" ' . $attributes . '>';