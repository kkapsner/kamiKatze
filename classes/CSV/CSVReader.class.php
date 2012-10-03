<?php
/**
 * CSVReader definition file
 */

/**
 * Reads CSV files. Accessing data lines is over the ArrayAccess interface.
 *
 * @author Korbinian Kapsner
 * @package CSV
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
	 * {@inheritdoc}
	 *
	 * @todo implement
	 * @param type $offset
	 */
	public function offsetExists($offset){
		
	}

	/**
	 * {@inheritdoc}
	 *
	 * @todo implement
	 * @param type $offset
	 */
	public function offsetGet($offset){
		
	}

	/**
	 * {@inheritdoc}
	 *
	 * @todo implement
	 * @param type $offset
	 * @param type $value
	 */
	public function offsetSet($offset, $value){
		
	}

	/**
	 * {@inheritdoc}
	 *
	 * @todo implement
	 * @param type $offset
	 */
	public function offsetUnset($offset){
		
	}

}

?>
