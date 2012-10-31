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
	 * @var string
	 */
	protected $linkField;
	/**
	 * The class specifier for the linke table.
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
		$subItem->{$this->linkField} = $id;
		$subItem->save();
		return $subItem;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param array $options
	 */
	protected function parseOptions(DBItemClassSpecifier $classSpecifier, $options){
		parent::parseOptions($classSpecifier, $options);

		$this->linkTable = array_read_key("linkTable", $options, $classSpecifier . "_" . $this->name . "ArrayData");
		$this->linkSpecifier = new DBItemClassSpecifier("DBItemFieldArrayItem", $this->linkTable);
		$this->linkField = array_read_key("linkField", $options, "link_id");
		$this->arrayFields = DBItemField::parseClass($this->linkTable);
		foreach ($this->arrayFields as $field){
			/* @var $field DBItemField */
			$field->parentField = $this;
			if ($field->name === $this->linkField){
				$field->editable = false;
				$field->displayable = false;
			}
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
		return DBItem::getByConditionCLASS(
			$this->linkSpecifier,
			DB::getInstance()->quote($this->linkField, DB::PARAM_IDENT) . " = " . $item->DBid
		);
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
}

?>