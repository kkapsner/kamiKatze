<?php
/* @var $this DBItemFieldDBDynamicItemOneToN */
/* @var $context string */
/* @var $args DBItem */

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