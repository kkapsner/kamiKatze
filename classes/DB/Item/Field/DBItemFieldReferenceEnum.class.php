<?php
/**
 * DBItemFieldReferenceEnum definition file
 */

/**
 * Representation of an "simulated" enum field. It's an enum that uses a
 * reference table to obtain the text representations.
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldReferenceEnum extends DBItemFieldEnum{
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param type $properties
	 */
	protected function adoptProperties(\DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);
		
		$referenceTable = array_read_key("referenceTable", $properties,
			DBItemClassSpecifier::$tablePrefix . $this->name . "_values");
		$referenceIdColumn = array_read_key("referenceIdColumn", $properties, "id");
		$referenceValueColumn = array_read_key("referenceValueColumn", $properties, "value");
		$referenceOrdering = array_read_key("referenceOrdering", $properties, null);
		
		$this->typeExtension = array();
		
		$db = DB::getInstance();
		foreach (
			$db->query("
				SELECT
					" . $db->quote($referenceIdColumn, DB::PARAM_IDENT) . ",
					" . $db->quote($referenceValueColumn, DB::PARAM_IDENT) . "
				FROM 
					" . $db->quote($referenceTable, DB::PARAM_IDENT) . "
				" . ($referenceOrdering? "ORDER BY " . $referenceOrdering: "") . "
			") as $valueRow
		){
			$this->typeExtension[$valueRow[$referenceIdColumn]] = $valueRow[$referenceValueColumn];
		}
	}
	
	/**
	 * Translates a given string value to the id in the reference table.
	 * 
	 * @param string $value
	 * @return int
	 */
	private function valueToId($value){
		return array_search($value, $this->typeExtension);
	}
	
	/**
	 * Translated a given id in the reference table to the string value.
	 * 
	 * @param int $id
	 * @return string
	 */
	protected function idToValue($id){
		return $this->typeExtension[$id];
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 * @return mixed
	 */
	public function getValue(DBItem $item){
		return $this->idToValue(parent::getValue($item));
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @param string $nameOut
	 * @param string|null $valueOut
	 */
	public function appendDBNameAndValueForCreate($value, &$nameOut, &$valueOut = null){
		parent::appendDBNameAndValueForCreate($this->valueToId($value), $nameOut, $valueOut);
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 * @param mixed $value
	 */
	public function setValue(DBItem $item, $value){
		parent::setValue($item, $this->valueToId($value));
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return mixed
	 */
	public function getWhere($value){
		return parent::getWhere($this->valueToId($value));
	}

}