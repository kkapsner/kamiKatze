<?php
/**
 * EmailHeaderContentType definition file
 */

/**
 * Email header for content type.
 *
 * @author Korbinian Kapsner
 * @package Email\Header
 */
class EmailHeaderContentType extends EmailHeaderParametric{
	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $name = "Content-Type";

	/**
	 * Constructor for EmailHeaderContentType
	 *
	 * @param string $value MIME-type
	 * @param array $parameter additional parameter
	 */
	public function __construct($value = "text/plain", array $parameter = NULL){
		parent::__construct("Content-Type", $value, $parameter);
	}

	/**
	 * This function does nothing! It has to be implementet because of inheritance.
	 *
	 * @param type $name
	 * @return false
	 */
	public function setName($name){
		return false;
	}

	/**
	 * {@nheritdoc}
	 *
	 * @param type $value
	 * @return boolean
	 */
	public function setValue($value){
		if (preg_match('/^[a-z]+\/[a-z]+$/i', $value)){
			$this->value = $value;
			return true;
		}
		return false;
	}
	
	/**
	 * Sets the main type.
	 *
	 * @param type $type
	 * @return bool if the type was set.
	 */
	public function setMainType($type){
		return $this->setValue($type . "/" . $this->getSubType());
	}
	
	/**
	 * Sets the sub type.
	 *
	 * @param type $type
	 * @return bool if the type was set.
	 */
	public function setSubType($type){
		return $this->setValue($this->getMainType() . "/" . $type);
	}
	
	/**
	 * Returns the MIME-type
	 *
	 * @return array containing the types: 0 => main type, 1 => sub type
	 */
	public function getTypes(){
		return explode("/", $this->value);
	}
	
	/**
	 * Return the main MIME-type
	 *
	 * @return string the main type
	 */
	public function getMainType(){
		$types = $this->getTypes();
		return $types[0];
	}
	
	/**
	 * Returns the sub MIME-type
	 *
	 * @return string the sub type
	 */
	public function getSubType(){
		$types = $this->getTypes();
		return $types[1];
	}
}

?>
