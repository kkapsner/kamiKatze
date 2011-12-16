<?php

/**
 * Description of EmailHeaderContentType
 *
 * @author kkapsner
 */
class EmailHeaderContentType extends EmailHeaderParametric{
	protected $name = "Content-Type";

	public function __construct($value = "text/plain", array $parameter = NULL){
		parent::__construct("Content-Type", $value, $parameter);
	}

	public function setName($name){
		return false;
	}

	public function setValue($value){
		if (preg_match('/^[a-z]+\/[a-z]+$/i', $value)){
			$this->value = $value;
			return true;
		}
		return false;
	}
	
	/**
	 * Sets the main type.
	 * @param type $type
	 * @return bool if the type was set.
	 */
	public function setMainType($type){
		return $this->setValue($type . "/" . $this->getSubType());
	}
	
	/**
	 * Sets the sub type.
	 * @param type $type
	 * @return bool if the type was set.
	 */
	public function setSubType($type){
		return $this->setValue($this->getMainType() . "/" . $type);
	}
	
	/**
	 * @return array containing the types: 0 => main type, 1 => sub type
	 */
	public function getTypes(){
		return explode("/", $this->value);
	}
	
	/**
	 * @return string the main type
	 */
	public function getMainType(){
		$types = $this->getTypes();
		return $types[0];
	}
	
	/**
	 * @return string the sub type
	 */
	public function getSubType(){
		$types = $this->getTypes();
		return $types[1];
	}
}

?>
