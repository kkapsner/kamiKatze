<?php
/**
 * CSVReader definition file
 */

/**
 * Reads CSV files. Accessing data lines is over the ArrayAccess interface.
 *
 * @author Korbinian Kapsner
 */
class CSVReader implements ArrayAccess{
	/**
	 * The used delimiter (default ",")
	 * @var string
	 */
	public $delimiter = ",";
	/**
	 * The used enclosure (default '"')
	 * @var string
	 */
	public $enclosure = '"';
	/**
	 * The used escape (default '\\')
	 * @var string
	 */
	public $escape = '\\';

	/**
	 * Constructor for CSVReader
	 *
	 * @todo implement
	 */
	public function __construct(){
		
	}

	/**
	 * Destructor for CSVReader
	 *
	 * @todo implement
	 */
	public function __destruct(){
		
	}

	/**
	 *
	 * @todo implement
	 */
	public function offsetExists($offset){
		
	}

	/**
	 *
	 * @todo implement
	 */
	public function offsetGet($offset){
		
	}

	/**
	 *
	 * @todo implement
	 */
	public function offsetSet($offset, $value){
		
	}

	/**
	 *
	 * @todo implement
	 */
	public function offsetUnset($offset){
		
	}

}

?>
