<?php
/* @var $this DBItemFieldDBDynamicItemNToOne */
/* @var $context string */
/* @var $args DBItem */

$postName = $this->getPostName($args);
$value = $this->getValue($args);

$groups = array();
foreach ($this->classField->typeExtension as $class){
	$groups[$class] = DBItem::getByConditionCLASS($class);
}
$this->viewByName(
	"DBItemFieldDBItem",
	"editField.grouped.select",
	true,
	array(
		"postName" => $postName,
		"groups" => $groups,
		"value" => $value
	)
);