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
class DBItemFieldExtender extends DBItemFieldEnum implements DBItemFieldHasSearchableSubcollection{
	
	/**
	 * An array with the fieldOptions for all posible field values (enum!). The keys are the field values and the
	 * entries are the FieldCollections
	 * @var array
	 */
	public $extensionFieldOptions = array();

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed[] $values
	 * @return DBItemValidationException[]
	 */
	public function validate($values){
		$errors = parent::validate($values);
		$extenderValue = array_read_key($this->name, $values, $this->default);
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
	 * @param array $options
	 */
	protected function parseOptions(DBItemClassSpecifier $classSpecifier, $options){
		parent::parseOptions($classSpecifier, $options);
		
		foreach ($this->typeExtension as $value){
			$this->extensionFieldOptions[$value] = DBItemField::parseClass($value);
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
			/* @var $extenderCollection DBItemFieldCollection */
			$extenderCollection->translateRequestData($data, $translatedData);
		}
	}

	/**
	 * Creates all depedencies of the item in other tables than the original one.
	 *
	 * @param int $id
	 * @param array $values
	 */
	protected function createDependencies($id, $values){
		$db = DB::getInstance();
		$keys = array($db->quote('id', DB::PARAM_IDENT));
		$dbValues = array($id);
		$extenderValue = array_read_key($this->name, $values, $this->default);
		if ($extenderValue !== null){
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/* @var $field DBItemField */
				if (array_key_exists($field->name, $values)){
					$value = $field->translateToDB($values[$field->name]);
					if ($value !== null){
						$keys[] = $db->quote($field->name, DB::PARAM_IDENT);
						$dbValues[] = $value;
					}
				}
			}
			$db->query("INSERT INTO " . $db->quote(DBItemClassSpecifier::$tablePrefix . $extenderValue, DB::PARAM_IDENT) . " (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $dbValues) . ")");

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
	protected function performAssignmentsAfterCreation(DBItem $item, $values){
		$extenderValue = $item->{$this->name};
		if ($extenderValue !== null){
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/* @var $field DBItemField */
				$field->performAssignmentsAfterCreation($item, $values);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 */
	protected function loadDependencies(DBItem $item){
		$extenderValue = $item->{$this->name};
		if ($item->DBid === 0){
			$data = array();
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/* @var $field DBItemField */
				$data[$field->name] = $field->default;
			}
		}
		else {
			if ($extenderValue !== null){
				$db = DB::getInstance();
				$data = $db->query(
					"SELECT * FROM " .
					$db->quote(DBItemClassSpecifier::$tablePrefix . $extenderValue, DB::PARAM_IDENT) .
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
			/* @var $field DBItemField */
			$field->loadDependencies($item);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return boolean
	 */
	protected function saveDependencies(DBItem $item){
		$extenderValue = $item->getRealValue($this);
		if ($extenderValue !== null){
			$db = DB::getInstance();
			$prop = array();
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/* @var $field DBItemField */
				if ($field->saveDependencies($item) && $item->realValueChanged($field)){
					$name = $field->name;
					$value = $item->getRealValue($field);
					$prop[] = $db->quote($name, DB::PARAM_IDENT) . " = " . ($value === null? "NULL": $db->quote($value));

					$item->makeRealNewValueOld($field);
				}
			}
			if (count($prop) !== 0){
				$db->query("UPDATE " . $db->quote(DBItemClassSpecifier::$tablePrefix . $extenderValue, DB::PARAM_IDENT) . " SET " . implode(", ", $prop) . " WHERE `id` = " . $item->DBid);
			}
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 */
	protected function deleteDependencies(DBItem $item){
		$extenderValue = $item->getRealValue($this);
		$db = DB::getInstance();
		if ($extenderValue !== null){
			$db->query("DELETE FROM  " . $db->quote(DBItemClassSpecifier::$tablePrefix . $extenderValue, DB::PARAM_IDENT) . " WHERE `id` = " . $item->DBid);
			foreach ($this->extensionFieldOptions[$extenderValue] as $field){
				/* @var $field DBItemField */
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

}

?>