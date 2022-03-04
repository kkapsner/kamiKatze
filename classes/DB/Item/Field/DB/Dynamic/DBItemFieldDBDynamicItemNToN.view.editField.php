<?php
/** @var DBItemFieldDBDynamicItemOneToN $this */
/** @var string $context */
/** @var DBItem $args */

$postName = $this->getPostName($args);
$value = $this->getValue($args);

$groups = array();
foreach ($this->class as $class){
	$groups[$class] = DBItem::getByConditionCLASS($class);
}
$this->viewByName(
	"DBItemFieldDBItem",
	"editField.grouped.multiSelect",
	true,
	array(
		"postName" => $postName,
		"groups" => $groups,
		"value" => $value
	)
);