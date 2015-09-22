<?php

/**
 * DBItemFieldGroupTrait definition file
 */

/**
 * Description of DBItemFieldGroupTrait
 *
 * @author kkapsner
 */
trait DBItemFieldGroupTrait{
	
	/**
	 * An array with the field options for the array entries.
	 * @var DBItemFieldCollection
	 */
	protected $groupFields = null;

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param array $options
	 */
	public function parseGroup($classSpecifier, $group){
		if ($this->groupFields !== null){
			throw new BadMethodCallException("parseGroup can only be called once.");
		}
		$this->groupFields = self::iterateForParseClass($classSpecifier, $group);
		foreach ($this->groupFields as $field){
			/* @var $field DBItemField */
			$field->parentField = $this;
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return null
	 */
	public function translateToDB($value){
		return null;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @return null
	 */
	public function translateNameToDB(){
		return null;
	}
}

?>
