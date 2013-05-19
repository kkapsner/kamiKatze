<?php

/*
 * CalendarProperty declaration file
 */

/**
 * A CalendarProperty is a property of a CalendarObject
 *
 * @author kkapsner
 */
class CalendarProperty extends ViewableImplementation implements IteratorAggregate{
	/**
	 *
	 * @var String Name of the property.
	 */
	public $name;
	
	/**
	 * Storage for the value of the property
	 * @var mixed the real value of the property
	 */
	public $value;
	
	/**
	 * Constructor for CalendarProperty
	 * @param String $name The name of the property
	 * @param mixed $value The initial value of the property
	 */
	public function __construct($name, $value = ""){
		$this->name = $name;
		$this->value = $value;
	}
	
	/**
	 * Internal storage for the parameter of the property
	 * @var array
	 */
	protected $parameter = array();
	
	/**
	 * magic getter function. Returns the value of a parameter if existing and
	 * null otherwise
	 * @param String $name the parameter name
	 * @return mixed the parameter value or null otherwise
	 */
	public function __get($name){
		$name = strToLower($name);
		if (array_key_exists($name, $this->parameter)){
			$this->parameter[$name];
		}
		else {
			return null;
		}
	}

	/**
	 * magic setter function. Sets a parameter of the property.
	 * @param String $name the name of the parameter
	 * @param mixed $value the value to be set
	 */
	public function __set($name, $value){
		$name = strToLower($name);
		$this->parameter[$name] = $value;
	}

	
	/**
	 * IteratorAggregate interface
	 */
	
	public function getIterator(){
		return new ArrayIterator($this->parameter);
	}

}

?>
