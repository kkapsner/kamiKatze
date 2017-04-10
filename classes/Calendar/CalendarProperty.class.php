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
	 * Flag if the value is raw and has to be outputted as is
	 * @var bool
	 */
	public $rawValue = false;
	
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
	
	/**
	 * Generates a parse exception.
	 * 
	 * @param string $message The exception message
	 * @param integer $position The position of the error in the parsed text.
	 * @return \InvalidArgumentException The gerenared exception.
	 */
	private static function getParseException($message, $position){
		return new InvalidArgumentException("Invalid vCal syntax: $message (position $position).");
	}
	
	/**
	 * Parses an input to a calendar property object.
	 * @param StringIterator|String $iterator The input
	 * @return CalendarProperty Returns the parsed calendar property object.
	 * @throws InvalidArgumentException on invalid syntax.
	 */
	public static function parse($iterator){
		if (is_string($iterator)){
			$iterator = new StringIterator($iterator);
		}
		$name = $iterator->goToNextNot("a-zA-Z0-9-");
		if ($name === ""){
			throw self::getParseException("missing name", $iterator->key());
		}
		
		$parameter = array();
		while ($iterator->current() === ";"){
			// read parameter
			
			$iterator->next();
			$pName = $iterator->goToNextNot("a-zA-Z0-9-");
			if ($pName === ""){
				throw self::getParseException("missing parameter name", $iterator->key());
			}
			if ($iterator->current() !== "="){
				throw self::getParseException("expected equal sign", $iterator->key());
			}
			$iterator->next();
			
			if ($iterator->current() === "\""){
				// quoted parameter value
				$iterator->next();
				$pValue = $iterator->goToNext("\"");
				if ($iterator->current() !== "\""){
					throw self::getParseException("expected double quote", $iterator->key());
				}
				$iterator->next();
			}
			else {
				$pValue = $iterator->goToNextNot("\x20\x09\x21\x23-\x2B\x2D-\x39\x3C-\x7E\x80-\xF8");
			}
			$parameter[strToLower($pName)] = $pValue;
		}
		
		if ($iterator->current() !== ":"){
			throw self::getParseException("expected semicolon", $iterator->key());
		}
		$iterator->next();
		
		$value = $iterator->goToNext("\r\n");
		$iterator->goToNextNot("\r\n");
		
		$property = new self($name, $value);
		$property->parameter = $parameter;
		return $property;
	}
}