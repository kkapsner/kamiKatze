<?php
/* @var $this DBItemFieldDBItem */
/* @var $context string */
/* @var $args DBItem */

$postName = $this->getPostName($args);

if (
	!$this->canOverwriteOthers &&
	(
		$this->correlation === DBItemFieldDBItem::ONE_TO_ONE ||
		$this->correlation === DBItemFieldDBItem::ONE_TO_N
	)
){
	$db = DB::getInstance();
	$fieldName = $db->quote($this->correlationName, DB::PARAM_IDENT);
	$availableItems = DBItem::getByConditionCLASS(
		$this->classSpecifier, $fieldName . " IS NULL OR (" . $fieldName . " = " . $args->DBid .
		($this->availableCondition? " AND " . $this->availableCondition: "") . 
		(
			($this->correlation === DBItemFieldDBItem::ONE_TO_N && $this->correlationCondition)?
			" AND " . $this->correlationCondition:
			""
		) .
		")"
	);
}
else {
	$availableItems = DBItem::getByConditionCLASS($this->classSpecifier, $this->availableCondition);
}

switch ($this->correlation){
	case DBItemFieldDBItem::ONE_TO_ONE: case DBItemFieldDBItem::N_TO_ONE:
		$this->view(
			"editField.select",
			true,
			array(
				"postName" => $postName,
				"availableItems" => $availableItems,
				"value" => $this->getValue($args)
			)
		);
		break;
	case DBItemFieldDBItem::N_TO_N:
		if ($this->canHaveMultipleLinks){
			$this->view(
				"editField.multipleLinks",
				true,
				array(
					"postName" => $postName,
					"availableItems" => $availableItems,
					"value" => $this->getValue($args)
				)
			);
			break;
		}
		if ($this->editWithCheckboxes){
			$this->view(
				"editField.multiCheckbox",
				true,
				array(
					"postName" => $postName,
					"availableItems" => $availableItems,
					"value" => $this->getValue($args)
				)
			);
			break;
		}
	case DBItemFieldDBItem::ONE_TO_N:
		$this->view(
			"editField.multiSelect",
			true,
			array(
				"postName" => $postName,
				"availableItems" => $availableItems,
				"value" => $this->getValue($args)
			)
		);
		break;
}