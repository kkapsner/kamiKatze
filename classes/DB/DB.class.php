<?php
/**
 * DB definition file
 */

/**
 * Extension to PDO
 *
 * @author Korbinian Kapsner
 * @package DB
 */

class DB extends PDO{
	/**
	 * Represents an identifier.
	 */
	const PARAM_IDENT = 6;

	/**
	 * The standard configuration for a DB connection. Stored in dbConfig.ini
	 * @var ConfigFile
	 */
	protected static $defaultConfig;

	/**
	 * Standard instance of the DB connection.
	 * @var DB
	 */
	protected static $instance;

	/**
	 * get the default DB instance.
	 * @return DB
	 */
	public static function getInstance(){
		if (self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param type $dsn
	 * @param type $username
	 * @param type $password
	 * @param array $driverOptions
	 */
	public function __construct($dsn = null, $username = null, $password = null, array $driverOptions = null){
		if (self::$defaultConfig === null){
			self::$defaultConfig = new ConfigFile("dbConfig.ini");
			self::$defaultConfig->load();
		}
		
		foreach (array("dsn", "username", "password", "driverOptions") as $name){
			if (${$name} === null){
				${$name} = self::$defaultConfig->{$name};
			}
		}
		
		parent::__construct($dsn, $username, $password, $driverOptions);
		$this->setAttribute(DB::ATTR_STATEMENT_CLASS, array("DBStatement", array()));
		$this->setAttribute(DB::ATTR_ORACLE_NULLS, DB::NULL_NATURAL);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $string
	 * @param int $parameter_type
	 * @return string
	 */
	public function quote($string, $parameter_type = DB::PARAM_STR){
		if ($parameter_type === self::PARAM_IDENT){
			return $this->escapeIdentifier($string);
		}
		else {
			return parent::quote($string, $parameter_type);
		}
	}

	/**
	 * escapes an indentifier apropriate (supported driver: mysql). Surrounding chars are included.
	 * 
	 * @param string $identifier
	 * @param bool $includeChar
	 * @return string
	 */
	public function escapeIdentifier($identifier, $includeChar = true){
		switch ($this->getAttribute(DB::ATTR_DRIVER_NAME)){
			case "mysql":
				return ($includeChar? "`": "") . str_replace(
						"`",
						"``",
						preg_replace(
								'/[^\x{0001}-\x{FFFF}]/u',
								"",
								$identifier
						)
				) . ($includeChar? "`": "");
				break;
			default:
				throw new DBException("Not implemented for this driver.");
		}
	}
}

?>