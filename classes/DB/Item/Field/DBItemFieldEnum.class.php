<?php
/**
 * DBItemFieldEnum definition file
 */

/**
 * Representation of an enum field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldEnum extends DBItemField{

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
		else {
			return in_array($value, $this->typeExtension);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed[] $values
	 * @return DBItemValidationException[]
	 */
	public function validate($values){
		$errors = parent::validate($values);
		if (array_key_exists($this->name, $values)){
			$value = $values[$this->name];
			if ($value !== null && !in_array($value, $this->typeExtension, true)){
				$errors[] = new DBItemValidationException(
					"Field " . $this->displayName . " must be one of " . implode(", ", $this->typeExtension) . " " . $this->regExp . " but '" . $value . "' provided.",
					DBItemValidationException::WRONG_VALUE,
					$this
				);
			}
		}
		return $errors;
	}
}

?>
