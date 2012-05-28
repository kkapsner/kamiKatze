<?php

/*
 *
 *
 */

/**
 * Description of DBItemFieldOption
 *
 * @author kkapsner
 */
class DBItemFieldOption extends ViewableHTML{
	const DB_ITEM = "DBItem";

	const ONE_TO_ONE = 0;
	const ONE_TO_N   = 1;
	const N_TO_ONE   = 2;
	const N_TO_N     = 3;

	static protected $classOptions = array();

	/**
	 *
	 * @var string
	 */
	public $name;
	/**
	 *
	 * @var string
	 */
	public $displayName;
	/**
	 *
	 * @var string
	 */
	public $regExp = null;
	/**
	 *
	 * @var string
	 */
	public $type = null;
	/**
	 *
	 * @var string
	 */
	public $typeExtension = null;
	/**
	 *
	 * @var bool
	 */
	public $null = false;
	/**
	 *
	 * @var mixed
	 */
	public $default = null;
	/**
	 *
	 * @var bool
	 */
	public $searchable = true;
	/**
	 *
	 * @var bool
	 */
	public $editable = true;
	/**
	 *
	 * @var string
	 */
	public $class = null;
	/**
	 *
	 * @var int
	 */
	public $correlation = null;
	/**
	 *
	 * @var string
	 */
	public $correlationName = null;


	/**
	 *
	 * @param array $result
	 * @return self
	 */
	protected static function parseResult($class, $result){
		$item = new self($result["Field"]);
		$item->null = $result['Null'] === "YES";
		$item->default = $result["Default"];
		if (preg_match('/^(.+?)\(([^\)]*)\)$/', $result["Type"], $m)){
			$item->type = $m[1];
			$item->typeExtension = $m[2];
			
			if ($item->type === "enum"){
				if (preg_match_all('/\'((?:\'{2}|[^\'])*)\'/', $item->typeExtension, $m)){
					$item->typeExtension = $m[1];
					foreach ($item->typeExtension as $i => $v){
						$item->typeExtension[$i] = str_replace("''", "'", $v);
					}
				
					$item->default = $item->typeExtension[0];
				}
				else {
					$item->typeExtension = array();
				}
			}
		}
		else {
			$item->type = $result['Type'];
		}

		$options = json_decode($result["Comment"], true);
		if (!is_array($options)){
			$options = array();
		}
		
		$item->regExp = array_read_key("regExp", $options, null);
		$item->class = array_read_key("class", $options, null);

		if ($item->class !== null){
			$item->type = self::DB_ITEM;
			$item->searchable = false;
			
			# determine correlation
			switch (strtolower(array_read_key("correlation", $options, "1to1"))){
				case "1to1": case "onetoone":
					$item->correlation = self::ONE_TO_ONE;
					break;
				case "1ton": case "oneton":
					$item->correlation = self::ONE_TO_N;
					break;
				case "nto1": case "ntoone":
					$item->correlation = self::N_TO_ONE;
					break;
				case "nton":
					$item->correlation = self::N_TO_N;
					break;
				default:
					$item->correlation = self::ONE_TO_ONE;
			}
			$item->correlationName = array_read_key("correlationName", $options, $class);
			$item->editable = array_read_key("editable", $options, true);
		}

		$item->searchable = array_read_key("searchable", $options, $item->searchable);
		$item->displayName = array_read_key("displayName", $options, $item->displayName);

		return $item;
	}

	/**
	 *
	 * @param string $class
	 * @return array of DBItemFieldOption
	 */
	public static function parseClass($class){
		if (!array_key_exists($class, self::$classOptions)){
			$db = DB::getInstance();
			$ret = array();

			foreach ($db->query("SHOW FULL COLUMNS FROM " . $db->quote(DBItem::$tablePrefix . $class, DB::PARAM_IDENT)) as $result){
				$name = $result["Field"];
				if ($name === "id"){
					continue;
				}

				$ret[$name] = self::parseResult($class, $result);
			}
			self::$classOptions[$class] = $ret;
		}
		return self::$classOptions[$class];
	}
	
	protected function __construct($name){
		$this->name = $name;
		$this->displayName = $name;
	}

	/**
	 * Checks if the $value is correct for the specific field.
	 * @param mixed $value
	 * @return bool
	 */
	public function isValidValue($value){
		if ($value === null){
			return $this->null;
		}

		switch ($this->type){
			case self::DB_ITEM:
				switch ($this->correlation){
					case self::ONE_TO_ONE: case self::N_TO_ONE:
						return is_a($value, $this->class);
						break;
					case self::ONE_TO_N: case self::N_TO_N:
						if (is_array($value)){
							$ok = true;
							foreach ($value as $item){
								if (!is_a($value, $this->class)){
									$ok = false;
									break;
								}
							}
							return $ok;
						}
						else {
							return false;
						}
						break;
				}
				break;
			case "enum":
				return in_array($value, $this->typeExtension);
				break;
			default:
				if ($this->regExp){
					return preg_match($this->regExp, $value);
				}
		}
		return true;
	}
}

?>
