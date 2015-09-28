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
	$db = DB::getInstance();
	$fieldName = $db->quote($this->correlationIdName, DB::PARAM_IDENT);
	if ($this->correlationClassName){
		$classFieldName = $db->quote($this->correlationClassName, DB::PARAM_IDENT);
		$correlationCondition = " AND " . $classFieldName . " = " . $db->quote(get_class($args), DB::PARAM_STR);
	}
	else {
		$correlationCondition = "";
	}
	foreach ($this->class as $i => $class){
		$groups[$class] = DBItem::getByConditionCLASS($this->classSpecifier[$i], 
		$fieldName . " IS NULL OR (" .
			$fieldName . " = " . $args->DBid .
			$correlationCondition . "
		)");
	}
}
$this->viewByName(
	"DBItemFieldDBDynamicItem",
	"editField.multiSelect",
	true,
	array(
		"postName" => $postName,
		"groups" => $groups,
		"value" => $value
	)
);