<?php
/* @var $this DBItemFieldDBDynamicItemOneToN */
/* @var $context string */
/* @var $args DBItem */

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