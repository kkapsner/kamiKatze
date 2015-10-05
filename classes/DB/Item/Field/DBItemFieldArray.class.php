<?php
/**
 * DBItemFieldArray definition file
 */

/**
 * Representation of an array field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldArray extends DBItemField{
	/**
	 * The linked table for the array.
	 * @var string
	 */
	protected $linkTable;
	/**
	 * The field in the linking table that contains the DBItems id.
	 * @var DBItemField
	 */
	protected $linkField;
	/**
	 * The class specifier for the link table.
	 * @var DBItemClassSpecifier
	 */
	protected $linkSpecifier;
	/**
	 * An array with the field options for the array entries.
	 * @var DBItemFieldCollection
	 */
	protected $arrayFields;
	
	/**
	 * The id of the subitem that is to be showed at the moment. Null indicates currently no iteration over the subitems.
	 * @var null|int
	 */
	protected $currentSubID = null;
	/**
	 * The current item. Outside an view iteration this is null.
	 * @var DBItem
	 */
	protected $currentItem = null;

	/**
	 * Creates one subitem with the specified values.
	 * 
	 * @param int $id the DBItems DBid
	 * @param mixed[] $values
	 * @return DBItem the new created subitem.
	 */
	protected function createSubitem($id, $values){
		$subItem = DBItem::createCLASS($this->linkSpecifier, $values, true);
		if ($this->linkField instanceof DBItemFieldDBDynamicItemNToOne){
			/* @var DBItemFieldDBDynamicItemNToOne $linkField */
			$linkField = $this->linkField;
			$subItem->setRealValue($linkField->idField->name, $id);
			$subItem->setRealValue($linkField->classField->name, $this->parentClassSpecifier->getClassName());
		}
		else {
			$subItem->setRealValue($this->linkField->name, $id);
		}
		$subItem->save();
		return $subItem;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param array $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);

		$this->linkTable = array_read_key("linkTable", $properties, $classSpecifier . "_" . $this->name . "ArrayData");
		$this->linkSpecifier = new DBItemClassSpecifier("DBItemFieldArrayItem", $this->linkTable);
		$this->arrayFields = DBItemField::parseClass($this->linkTable);
		$this->linkField = $this->arrayFields->getFieldByName(
			array_read_key("linkField", $properties, "link_id")
		);
		if (!$this->linkField){
			throw new BadMethodCallException("Linking field not found.");
		}
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param int $id
	 * @param mixed[] $values
	 */
	protected function createDependencies($id, $values){
		if (array_key_exists($this->name, $values)){
			foreach ($values[$this->name]["create"] as $value){
				$this->createSubitem($id, $value);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 */
	protected function deleteDependencies(DBItem $item){
		foreach ($this->getValue($item) as $subItem){
			/* @var $subItem DBItem */
			$subItem->delete();
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $dbItem
	 * @param bool $nameArray
	 * @return string
	 * @todo implement!
	 */
	protected function getPostName(DBItem $dbItem, $nameArray = false){
		if ($this->currentSubID === null){
			return parent::getPostName($dbItem, $nameArray);
		}
		else {
			if ($nameArray){
				$arr = parent::getPostName($this->currentItem, $nameArray);
				$arr[] = $this->currentSubID;
				$arr[] = "data";
				return $arr;
			}
			else {
				return parent::getPostName($this->currentItem, $nameArray) . "[" . $this->currentSubID ."][data]";
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 * @return DBItemCollection
	 */
	public function getValue(DBItem $item){
		$db = DB::getInstance();
		if ($this->linkField instanceof DBItemFieldDBDynamicItemNToOne){
			/* @var DBItemFieldDBDynamicItemNToOne $linkField */
			
			return DBItem::getByConditionCLASS(
				$this->linkSpecifier,
				$db->quote($this->linkField->idField->name, DB::PARAM_IDENT) . " = " .
				$item->DBid . " AND " .
				$db->quote($this->linkField->classField->name, DB::PARAM_IDENT) . " = " .
				$db->quote(get_class($item), DB::PARAM_STR)
			);
		}
		else {
			return DBItem::getByConditionCLASS(
				$this->linkSpecifier,
				DB::getInstance()->quote($this->linkField->name, DB::PARAM_IDENT) . " = " . $item->DBid
			);
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param type $value
	 * @return type
	 * @todo implement
	 */
	public function isValidValue($value){
		return parent::isValidValue($value);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 */
	protected function saveDependencies(DBItem $item){
		foreach ($this->getValue($item) as $subItem){
			/* @var $subItem DBItem */
			$subItem->save();
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param type $value
	 */
	public function setValue(DBItem $item, $value){
		foreach ($value["create"] as $v){
			/* @var $v DBItem */
			$this->createSubitem($item->DBid, $v);
		}
		foreach ($value["modify"] as $v){
			$item = DBItem::getCLASS($this->linkSpecifier, $v["id"]);
			foreach ($v as $name => $d){
				if ($name !== "id"){
					$item->{$name} = $d;
				}
			}
			$item->save();
		}
		foreach ($value["delete"] as $v){
			$v->delete();
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array $data
	 * @param array $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		if (array_key_exists($this->name, $data)){
			$translatedData[$this->name] = array(
				"create" => array(),
				"modify" => array(),
				"delete" => new DBItemCollection($this->linkSpecifier->getClassName())
			);
			$value = $data[$this->name];
			foreach ($value as $v){
				if (is_array($v) && array_key_exists("data", $v) && is_array($v["data"])){
					$subitem = $v["data"];
					switch (array_read_key("action", $v, "modify")){
						case "delete":
							if (array_key_exists("id", $subitem)){
								$translatedData[$this->name]["delete"][] = DBItem::getCLASS($this->linkSpecifier, $subitem["id"]);
							}
							break;
						case "create":
							$subData = $this->arrayFields->translateRequestData($subitem);
							$translatedData[$this->name]["create"][] = $subData;
							break;
						default:
							$subData = $this->arrayFields->translateRequestData($subitem);
							$subData["id"] = $subitem["id"];
							$translatedData[$this->name]["modify"][] = $subData;
					}
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return null
	 */
	public function translateToDB($value){
		return null;
	}
	
	/**
	 * {@inheritdoc}
	 *
	 * @param string $context
	 * @param boolean $output
	 * @param mixed $args
	 * @return string|boolean
	 */
	public function view($context = false, $output = false, $args = false){
		$oldDisplayable = $this->linkField->displayable;
		$this->linkField->displayable = false;
		$ret = parent::view($context, $output, $args);
		$this->linkField->displayable = $oldDisplayable;
		return $ret;
	}

}

?>
