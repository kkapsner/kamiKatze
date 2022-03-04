<?php
/** @var DBItemFieldDBDynamicItemOneToN $this */
/** @var string $context */
/** @var DBItem $args */

$postName = $this->getPostName($args);
$value = $this->getValue($args);

$groups = array();
if ($this->canOverwriteOthers){
	foreach ($this->class as $class){
		$groups[$class] = DBItem::getByConditionCLASS($class);
	}
}
else {
	foreach ($this->class as $i => $class){
		$groups[$class] = DBItem::getByConditionCLASS($this->classSpecifier[$i], 
		"(" . $this->correlationField[$i]->getWhere(null) . ") OR (" . $this->correlationField[$i]->getWhere($args) . ")");
	}
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