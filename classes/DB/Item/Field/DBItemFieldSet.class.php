<?php
/**
 * DBItemFieldSet definition file
 */

/**
 * Representation of a set field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldSet extends DBItemFieldNative{

	/**
	 * {@inheritdoc}
	 *
	 * @param array $data
	 * @param array $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		if (array_key_exists($this->name, $data)){
			$data = $data[$this->name];
			if (is_array($data) && array_key_exists("present", $data)){
				$value = array_read_key("values", $data, $this->null? null: array());
				$translatedData[$this->name] = $value;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param type $value
	 * @return string|null
	 */
	public function translateToDB($value){
		if (is_array($value)){
			$value = implode(",", $value);
		}
		return parent::translateToDB($value);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return type
	 */
	public function getValue(DBItem $item){
		$realValue = $item->getRealValue($this);
		if ($realValue === null){
			return null;
		}
		elseif ($realValue === ""){
			return array();
		}
		else {
			return explode(",", $item->getRealValue($this));
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 * @param type $value
	 * @throws InvalidArgumentException
	 */
	public function setValue(DBItem $item, $value){
		if ($value === null){
			$item->setRealValue($this->name, $value);
		}
		elseif (!is_array($value)){
			throw new InvalidArgumentException("Property " . $this->name . " is no array.");
		}
		else {
			$item->setRealValue($this->name, implode(",", $value));
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function isValidValue($value){
		if (!parent::isValidValue($value)){
			return false;
		}
		elseif (!is_array($value)){
			return false;
		}
		else {
			foreach ($value as $v){
				if (!in_array($v, $this->typeExtension)){
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed[] $values
	 * @return DBItemValidationException[]
	 */
	public function validate($values){
		$errors = parent::validate($values);
		if (
			array_key_exists($this->name, $values) &&
			array_key_exists("values", $values[$this->name])
		){
			$value = $values[$this->name]["values"];
			if ($value !== null){
				if (!is_array($value)){
					$errors[] = new DBItemValidationException(
						"Field " . $this->displayName . " must be an array.",
						DBItemValidationException::WRONG_VALUE,
						$this
					);
				}
				else {
					foreach ($value as $v){
						if (!in_array($v, $this->typeExtension, true)){
							$errors[] = new DBItemValidationException(
								"Field " . $this->displayName . " must be an array containing only " . implode(", ", $this->typeExtension) . " " . $this->regExp . " but '" . $v . "' provided.",
								DBItemValidationException::WRONG_VALUE,
								$this
							);
						}
					}
				}
			}
		}
		return $errors;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return string
	 */
	public function getWhere($value){
		if (!(count($value))){
			return "1 = 1";
		}
		$db = DB::getInstance();
		$name = $db->quote($this->name, DB::PARAM_IDENT);
		foreach ($value as &$v){
			$v = "FIND_IN_SET(" . $db->quote($v) . ", " . $name . ") > 0";
		}
		return implode(" AND ", $v);
	}

}

?>
