<?php
/**
 * DBItemClassSpecifier definition file
 */

/**
 * A specifier for a DBItem class.
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
class DBItemClassSpecifier{
	// static

	/**
	 * Prefix of table name
	 * @var string
	 */
	public static $tablePrefix = "";

	/**
	 * Creates a DBItemClassSpecifier from a specified name.
	 * @param string $specifiedName
	 * @return DBItemClassSpecifier
	 */
	public static function fromSpecifiedName($specifiedName){
		$parts = explode(".", $specifiedName, 2);
		if (count($parts) > 1){
			return new self($parts[0], $parts[1]);
		}
		else {
			return new self($parts[0]);
		}
	}

	/**
	 * Makes an input to a DBItemClassSpecifier.
	 * 
	 * @param DBItemClassSpecifier|string $specifiedNameOrSpecifier
	 * @return DBItemClassSpecifier
	 */
	public static function make($specifiedNameOrSpecifier){
		if ($specifiedNameOrSpecifier instanceof self){
			return $specifiedNameOrSpecifier;
		}
		else {
			return self::fromSpecifiedName($specifiedNameOrSpecifier);
		}
	}
	// non static

	/**
	 * The class name.
	 * @var string
	 */
	private $className;

	/**
	 * The table name.
	 * @var string
	 */
	private $tableName;

	/**
	 * Constructor for DBItemClassSpecifier
	 * @param string $className
	 * @param string $tableName
	 */
	public function __construct($className, $tableName = null){
		$this->className = $className;
		if ($tableName === null){
			if (property_exists($className, "tableName")){
				$this->tableName = self::$tablePrefix . $className::$tableName;
			}
			else {
				$this->tableName = self::$tablePrefix . $className;
			}
		}
		else {
			$this->tableName = $tableName;
		}
	}

	/**
	 * Getter for the class name.
	 * @return string
	 */
	public function getClassName(){
		return $this->className;
	}

	/**
	 * Getter for the table name.
	 * @return string
	 */
	public function getTableName(){
		return $this->tableName;
	}

	/**
	 * Returns the specified name that encodes the class and the table name.
	 * @return string
	 */
	public function getSpecifiedName(){
		return $this->className . "." . $this->tableName;
	}

	/**
	 * Magic __toString function. Returns the specified name.
	 * 
	 * @return string
	 */
	public function __toString(){
		return $this->getSpecifiedName();
	}

}

?>
