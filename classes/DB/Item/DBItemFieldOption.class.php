<?php
/*
 * DBItemFieldOption class definition
 *
 */

/**
 * Options and properties managing class for a field.
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
class DBItemFieldOption extends ViewableHTML{
	const DB_ITEM = "DBItem";

	const ONE_TO_ONE = 0;
	const ONE_TO_N   = 1;
	const N_TO_ONE   = 2;
	const N_TO_N     = 3;

	/**
	 * Cache array for already parsed classes.
	 * @var array
	 */
	static protected $classOptions = array();

	/**
	 * Name of the field.
	 * @var string
	 */
	public $name;
	/**
	 * Name to be displayed.
	 * @var string
	 */
	public $displayName;
	/**
	 * Regular expression that a value must match.
	 * @var string
	 */
	public $regExp = null;
	/**
	 * The type of the field
	 * @var string
	 */
	public $type = null;
	/**
	 * The type extension (part in the brackets). If the field is an enum or set ths is an array of the possible values.
	 * @var mixed
	 */
	public $typeExtension = null;
	/**
	 * If null is a valid value.
	 * @var bool
	 */
	public $null = false;
	/**
	 * The default value.
	 * @var mixed
	 */
	public $default = null;
	/**
	 * If a search can be performed on this field.
	 * @var bool
	 */
	public $searchable = true;
	/**
	 * If this field can be changed.
	 * @var bool
	 */
	public $editable = true;
	/**
	 * If this is not null this field represents another DBItem with this class.
	 * @var string
	 */
	public $class = null;
	/**
	 * The correlation between this DBItem and the other one.
	 * @var int
	 */
	public $correlation = null;
	/**
	 * The field name of this DBItem in the other one.
	 * @var string
	 */
	public $correlationName = null;

	/**
	 * Indicates if the field is a extender. I.e. the dataset will be extended by the field of a table thats name is
	 * stored in this field. This field has to be a enum.
	 * @var bool
	 */
	public $extender = false;
	/**
	 * An array with the fieldOptions for all posible field values (enum!). The keys are the field values and the
	 * entries are the FieldOptions
	 * @var array 
	 */
	public $extensionFieldOptions = array();


	/**
	 * Generates the field options from a DB result.
	 * @param string $class The class name
	 * @param array $result The results to parse
	 * @return self
	 */
	protected static function parseResult($class, $result){
		$item = new self($result["Field"]);
		$item->null = $result['Null'] === "YES";
		$item->default = $result["Default"];
		if (preg_match('/^(.+?)\(([^\)]*)\)$/', $result["Type"], $m)){
			$item->type = $m[1];
			$item->typeExtension = $m[2];
			
			if ($item->type === "enum" || $item->type === "set"){
				if (preg_match_all('/\'((?:\'{2}|[^\'])*)\'/', $item->typeExtension, $m)){
					$item->typeExtension = $m[1];
					foreach ($item->typeExtension as $i => $v){
						$item->typeExtension[$i] = str_replace("''", "'", $v);
					}
				
					#$item->default = $item->typeExtension[0];
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
		}

		$item->editable = array_read_key("editable", $options, true);
		$item->searchable = array_read_key("searchable", $options, $item->searchable);
		$item->displayName = array_read_key("displayName", $options, $item->displayName);

		if ($item->type === "enum"){
			$item->extender = array_read_key("extender", $options, $item->extender);
			if ($item->extender){
				foreach ($item->typeExtension as $value){
					$item->extensionFieldOptions[$value] = DBItemFieldOption::parseClass($value);
				}
			}
		}

		return $item;
	}

	/**
	 * Gets the field options for a specific class.
	 * @param string $class The class name
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

	/**
	 * Constructor of DBItemFieldOption.
	 */
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
