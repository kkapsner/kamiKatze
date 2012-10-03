<?php
/**
 * BBCodeTag definition file
 */

/**
 * Base class for all BBCodeTags.
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
abstract class BBCodeTag extends Node{
	/**
	 * The type of the tag.
	 * @var string
	 */
	protected static $type = "block";

	/**
	 * Array of allowed children.
	 * @var array
	 */
	protected static $allowedChildren = array("block", "inline");

	/**
	 * Array of allowed parents.
	 * @var array
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * Cache for self::getTagTypeInfo()
	 * @var array
	 */
	private static $tagTypeInfo = array();

	/**
	 * Returns the static attributes of the class according to a tag name.
	 * @param type $tagName
	 * @return array
	 */
	private static function getTagTypeInfo($tagName){
		if (!array_key_exists($tagName, self::$tagTypeInfo)){
			self::$tagTypeInfo[$tagName] = get_class_vars(self::tagNameToClass($tagName));
		}
		return self::$tagTypeInfo[$tagName];
	}
	
	/**
	 * Returns the classname to the given tagname.
	 * @param string $tagName
	 * @return string
	 */
	private static function tagNameToClass($tagName){
		return "BBCodeTag" . ucfirst(BBCodeAliasses::getRealTagFor(strToLower($tagName)));
	}
	
	/**
	 * Returns the tagname to the given classname.
	 * @param string $className
	 * @return string
	 */
	private static function classNameToTag($className){
		return strToLower(substr($className, 9));
	}

	/**
	 * Checks if a tagname exists.
	 * @param string $tagName
	 * @return bool
	 */
	public static function tagExists($tagName){
		if ($tagName === ""){
			return false;
		}
		return class_exists(self::tagNameToClass($tagName));
	}

	/**
	 * Creates a tag instance from the given tagname and with the given parameter.
	 * @param string $tagName
	 * @param array $parameter
	 * @return BBCodeTag
	 */
	public static function createTag($tagName, array $parameter = array()){
		$tagName = strToLower($tagName);
		$classname = self::tagNameToClass($tagName);
		/* @var $tag BBCodeTag*/
		$tag = new $classname($parameter);
		$tag->tagName = $tagName;
		return $tag;
	}

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array();

	/**
	 * The tagname.
	 * @var string
	 */
	protected $tagName = NULL;

	/**
	 * The real tagname (no alias).
	 * @var string
	 */
	private $realTagName = NULL;

	/**
	 * Constructor for BBCodeTag
	 * @param array $parameter
	 */
	public function __construct(array $parameter = array()){
		foreach ($parameter as $k => $v){
			$this->{$k} = $v;
		}
		$this->realTagName = $this->tagName = self::classNameToTag(get_class($this));
	}

	/**
	 * Checks if the tag can contain an other tag with tagname $tagname.
	 * @param type $tagName
	 * @return bool
	 */
	public function canContain($tagName){
		$thisInfo = self::getTagTypeInfo($this->realTagName);
		$tagName = BBCodeAliasses::getRealTagFor($tagName);
		$tagInfo = self::getTagTypeInfo($tagName);
		return (
				in_array($tagInfo["type"], $thisInfo["allowedChildren"]) ||
				in_array($tagName, $thisInfo["allowedChildren"])
			) && (
				in_array($thisInfo["type"], $tagInfo["allowedParents"]) ||
				in_array($this->realTagName, $tagInfo["allowedParents"])
			) &&
			!in_array("!" . $tagName, $thisInfo["allowedChildren"]) &&
			!in_array("!" . $this->realTagName, $tagInfo["allowedParents"])
		;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param Node $newChild
	 * @return boolean
	 */
	public function appendChild(Node $newChild){
		if (
			$newChild instanceof BBCodeTag &&
			$this->canContain($newChild->tagName)
		){
			return parent::appendChild($newChild);
		}
		return false;
	}

	/**
	 * Generates pain text.
	 * @return string
	 */
	public function toText(){
		return $this->childrenToText();
	}
	/**
	 * Generates plain text from the children.
	 * @return string
	 */
	public function childrenToText(){
		$ret = "";
		foreach ($this as $child){
			/* @var $child BBCodeTag */
			$ret .= $child->toText();
		}
		return $ret;
	}

	/**
	 * Generates valid BB-code.
	 * @return string
	 */
	public function toBBCode(){
		$ret = "[" . $this->tagName;
		foreach ($this->parameter as $k => $v){
			if ($v === true){
				$ret .= " " . $k;
			}
			else {
				$ret .= " " . $k . "=" . '"' . str_replace('"', '\"', str_replace("\\", "\\\\", $v)) . '"';
			}
		}
		if ($this->count() === 0){
			$ret .= " /]";
		}
		else {
			$ret .= "]" . $this->childrenToBBCode() . "[/" . $this->tagName . "]";
		}
		return $ret;
	}
	/**
	 * Generates valid BB-code from the children.
	 * @return string
	 */
	public function childrenToBBCode(){
		$ret = "";
		foreach ($this as $child){
			/* @var $child BBCodeTag */
			$ret .= $child->toBBCode();
		}
		return $ret;
	}

	/**
	 * Generates HTML.
	 * @return string
	 */
	abstract public function toHTML();

	/**
	 * Generates HTML from the children.
	 * @return string
	 */
	public function childrenToHTML(){
		$ret = "";
		foreach ($this as $child){
			/* @var $child BBCodeTag */
			$ret .= $child->toHTML();
		}
		return $ret;
	}

	/**
	 * Only known parameter are set. Unknown parameter are silently ignored.
	 * @param string $name
	 * @param mixed $value 
	 */
	public function __set($name, $value){
		$name = strtolower($name);
		if (array_key_exists($name, $this->parameter)){
			$this->parameter[$name] = $value;
		}
		else {
			$alias = BBCodeAliasses::getRealParameterFor($name);
			if ($alias !== $name){
				$this->{$alias} = $value;
			}
		}
	}

	/**
	 * If a parameter is known its value is returned. An unknown parameter returns NULL.
	 * @param string $name
	 */
	public function __get($name){
		if (array_key_exists($name, $this->parameter)){
			return $this->parameter[$name];
		}
		return NULL;
	}

	/**
	 * Returns the tag name in the code (can be an alias).
	 * @return string the tagName.
	 */
	public function getTagName(){
		return $this->tagName;
	}

	/**
	 * Returns the real tag name.
	 * @return string the real tagName (no alias).
	 */
	public function getRealTagName(){
		return $this->realTagName;
	}

	/**
	 * Searches for the charset of the parser.
	 * @return string the used charset. Or "utf-8" if nothing is found
	 */
	public function getCharset(){
		$root = $this->getRoot();
		if ($root !== NULL && $root instanceof BBCodeTagRoot){
			/* @var $root BBCodeTagRoot */
			if ($root->parser !== NULL){
				return $root->parser->charset;
			}
		}
		return "utf-8";
	}
}

?>
