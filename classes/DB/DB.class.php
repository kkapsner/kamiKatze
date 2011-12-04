<?php

/**
 * Extension to PDO
 */

class DB extends PDO{
	const PARAM_IDENT = 6;
	protected static
		$defaultConfig,
		$instance
	;
	
	public static function getInstance(){
		if (self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
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