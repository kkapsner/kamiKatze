<?php
/**
 * DBItemFieldDBItem definition file
 */

/**
 * Representation of a DBItem field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
abstract class DBItemFieldDBItem extends DBItemField implements DBItemFieldSearchable{
	const ONE_TO_ONE = 0;
	const ONE_TO_N   = 1;
	const N_TO_ONE   = 2;
	const N_TO_N     = 3;
	private static $classNames = array(
		"DBItemFieldDBItemOneToOne",
		"DBItemFieldDBItemOneToN",
		"DBItemFieldDBItemNToOne",
		"DBItemFieldDBItemNToN",
	);
	
	public static function unifyCorrelation($correlation){
		switch (strtolower($correlation)){
			case "1to1": case "onetoone":
				return self::ONE_TO_ONE;
				break;
			case "1ton": case "oneton":
				return self::ONE_TO_N;
				break;
			case "nto1": case "ntoone":
				return self::N_TO_ONE;
				break;
			case "nton":
				return self::N_TO_N;
				break;
			default:
				return self::ONE_TO_ONE;
		}
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param array $properties
	 * @return DBItemFieldDBItem
	 */
	
	protected static function create(DBItemClassSpecifier $classSpecifier, $properties){
		$properties["correlation"] = self::unifyCorrelation(
			array_read_key("correlation", $properties, "1to1")
		);
		$className = self::$classNames[$properties["correlation"]];
		$item = new $className($properties["name"]);
		$item->adoptProperties($classSpecifier, $properties);
		return $item;
	}
	
	/**
	 * The specifier of the connected DBItem.
	 * @var DBItemClassSpecifier
	 */
	protected $classSpecifier = null;
	
	/**
	 * If this is not null this field represents another DBItem with this class.
	 * @var string
	 */
	public $class = null;
	public function setClass($newClass){
		$this->class = $newClass;
		$this->classSpecifier = DBItemClassSpecifier::make($newClass);
	}
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
	 * If a change in this field can overwrite this field in an other item.
	 * @var boolean
	 */
	public $canOverwriteOthers = false;
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);
		
		$this->correlation = $properties["correlation"];
		$this->correlationName = array_read_key("correlationName", $properties, $classSpecifier->getClassName());
		$this->canOverwriteOthers = array_read_key("canOverwriteOthers", $properties, $this->canOverwriteOthers);
		
		$this->setClass(array_read_key("class", $properties, $this->class));

		// disable default options...
		$this->searchable = false;
		$this->regExp = null;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @param string $nameOut
	 * @param string|null $valueOut
	 */
	public function appendDBNameAndValueForCreate($value, &$nameOut, &$valueOut = null){
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param type $value
	 * @param string $propsOut
	 */
	public function appendDBNameAndValueForUpdate($value, &$propsOut){
		parent::appendDBNameAndValueForCreate($value, $propsOut);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param array $values
	 */
	protected function performAssignmentsAfterCreation(DBItem $item, $values){
		if (array_key_exists($this->name, $values)){
			$item->{$this->name} = $values[$this->name];
		}
	}
}