<?php
/** @var DBItemFieldDBDynamicItemNToOne $this */
/** @var string $context */
/** @var DBItem $args */

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