<?php
/**
 * DBItemFieldExtender definition file
 */

/**
 * Representation of an enum extender field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
trait DBItemFieldExtenderTrait{
	
	/**
	 * An array with the fieldOptions for all posible field values (enum!). The keys are the field values and the
	 * entries are the FieldCollections
	 * @var array
	 */
	public $extensionFieldOptions = array();
	
	/**
	 * Format for the table names that contain the extender information.
	 * @var String|null
	 */
	public $extenderTableFormat = null;

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed[] $values
	 * @return DBItemValidationException[]
	 */
	public function validate($values){
		$errors = parent::validate($values);
		$extenderValue = array_read_key($this->name, $values, $this->getDefault());
		if ($extenderValue !== null){
			$errors = array_merge(
				$errors,
				$this->extensionFieldOptions[$extenderValue]->validate($values)
			);
		}
		return $errors;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param array $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);
		
		if (array_key_exists("extenderFormat", $properties)){
			$this->extenderTableFormat = $properties["extenderFormat"];
		}
		
		foreach ($this->typeExtension as $value){
			$this->extensionFieldOptions[$value] = DBItemField::parseClass(new DBItemClassSpecifier($value, $this->getTableName($value)));
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array $data
	 * @param array $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		parent::translateRequestData($data, $translatedData);

		foreach ($this->extensionFieldOptions as $extenderCollection){
			/** @var DBItemFieldCollection $extenderCollection */
			$extenderCollection->translateRequestData($data, $translatedData);
		}
	}

	/**
	 * Creates all depedencies of the item in other tables than the original one.
	 *
	 * @param int $id
	 * @param array $values
	 */
	public function createDependencies($id, $values){
		$db = $this->getDB();
		$keys = $db->quote('id', DB::PARAM_IDENT);
		$dbValues = $id;
		$extenderValue = array_read_key($this->name, $values, $this->default);
		if ($extenderValue !== null){
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/** @var DBItemField $field */
				if (array_key_exists($field->name, $values)){
					$field->appendDBNameAndValueForCreate($values[$field->name], $keys, $dbValues);
				}
			}
			$db->query("INSERT INTO " . $db->quote($this->getTableName($extenderValue), DB::PARAM_IDENT) . " (" . $keys . ") VALUES (" . $dbValues . ")");
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				$field->createDependencies($id, $values);
			}
		}
	}

	/**
	 * Performs assigments that have to occure after the creation of an item.
	 *
	 * @param DBItem $item
	 * @param type $values
	 */
	public function performAssignmentsAfterCreation(DBItem $item, $values){
		$extenderValue = $item->{$this->name};
		if ($extenderValue !== null){
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/** @var DBItemField $field */
				$field->performAssignmentsAfterCreation($item, $values);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 */
	public function loadDependencies(DBItem $item){
		$extenderValue = $this->getValue($item);
		if ($item->DBid === 0){
			$data = array();
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/** @var DBItemField $field */
				$data[$field->name] = $field->default;
			}
		}
		else {
			if ($extenderValue !== null){
				$db = $this->getDB();
				$data = $db->query(
					"SELECT * FROM " .
					$db->quote($this->getTableName($extenderValue), DB::PARAM_IDENT) .
					"WHERE `id` = " . $item->DBid
				);
				$data = $data->fetch(DB::FETCH_ASSOC);
				if (!$data){
					throw new Exception("Invalid database. Please contact administrator. (ID " . $item->DBid . " not found in extender table " . $extenderValue . ")");
				}
			}
		}
		foreach ($data as $k => $v){
			$item->setRealValue($k, $v);
		}
		foreach ($this->extensionFieldOptions[$extenderValue] as $field){
			/** @var DBItemField $field */
			$field->loadDependencies($item);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return boolean
	 */
	public function saveDependencies(DBItem $item){
		$extenderValue = $this->getValue($item);
		if ($extenderValue !== null){
			$db = $this->getDB();
			$prop = "";
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/** @var DBItemField $field */
				if ($field->saveDependencies($item) && $item->realValueChanged($field)){
					$field->appendDBNameAndValueForUpdate($item->getRealValue($field), $prop);
					$item->makeRealNewValueOld($field);
				}
			}
			if (strlen($prop) !== 0){
				$db->query("UPDATE " . $db->quote($this->getTableName($extenderValue), DB::PARAM_IDENT) . " SET " . $prop . " WHERE `id` = " . $item->DBid);
			}
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 */
	public function deleteDependencies(DBItem $item){
		$extenderValue = $this->getValue($item);
		$db = $this->getDB();
		if ($extenderValue !== null){
			$db->query("DELETE FROM  " . $db->quote($this->getTableName($extenderValue), DB::PARAM_IDENT) . " WHERE `id` = " . $item->DBid);
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/** @var DBItemField $field */
				$field->deleteDependencies($item);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param mixed $value
	 * @throws InvalidArgumentException
	 */
	public function setValue(DBItem $item, $value){
		throw new InvalidArgumentException("Extenders can not be changed.");
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @return DBItemFieldCollection[]
	 */
	public function getAllSubcollections(){
		$ret = array();
		foreach ($this->typeExtension as $value){
			$ret[] = $this->extensionFieldOptions[$value];
		}
		return $ret;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return DBItemFieldCollection|null
	 */
	public function getSubcollection(DBItem $item){
		$value = $item->{$this->name};
		if ($value !== null){
			return $this->extensionFieldOptions[$value];
		}
		else {
			return null;
		}
	}
	
	/**
	 * Returns the table name for the extended properties.
	 * 
	 * @param String $value The value of the extender field.
	 * @return String The name of the corresonding extender table.
	 */
	protected function getTableName($value){
		if ($this->extenderTableFormat){
			return DBItemClassSpecifier::$tablePrefix . sprintf($this->extenderTableFormat, $value);
		}
		else {
			return DBItemClassSpecifier::$tablePrefix . $value;
		}
	}
}