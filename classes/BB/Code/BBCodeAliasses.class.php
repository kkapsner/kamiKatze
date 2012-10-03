<?php
/**
 * BBCodeAliasses definition file
 */

/**
 * No instance class for alias table of BBCode-tags and BBCode-parameter.
 *
 * @author Korbinian Kapser
 * @package BB\Code
 */
class BBCodeAliasses{

	/**
	 * Private constructor of BBCodeAliasses that can not be invoced.
	 */
	private function __construct(){}

	/**
	 * The tag alias table
	 * @var array
	 */
	private static $tagAliasTable = array(
		"italic" => "i",
		"kursiv" => "i",
		"k" => "i",

		"bold" => "b",
		"fett" => "b",
		"f" => "b",

		"titel" => "title",
		"untertitel" => "subtitle",
		"überschrift" => "heading",
		"unterüberschrift" => "subheading",

		"zitat" => "quote",
		"code" => "code",
		"absatz" => "p",
		"paragraph" => "p",
		"liste" => "list",
		#"element" => "element",
		"tabelle" => "table",
		#"link" => "link",
		"bild" => "image",

		"justifier" => "j",

		#"youtube" => "youtube",
		"phpfunktion" => "phpfunction",
		"karte" => "map"
	);

	/**
	 * Adds an tag alias to the table.
	 * @param string $alias the alias tag name
	 * @param string $tag the real tag name
	 */
	public static function addTagAlias($alias, $tag){
		self::$tagAliasTable[$alias] = $tag;
	}

	/**
	 * Applies the tag alias table on the provided tag name.
	 * @param string $tagName the tag name to search for
	 * @return string Returns the real tag name if an alias is found. Otherwise $tagName is returned unmodified
	 */
	public static function getRealTagFor($tagName){
		if (array_key_exists($tagName, self::$tagAliasTable)){
			return self::$tagAliasTable[$tagName];
		}
		else {
			return $tagName;
		}
	}

	/**
	 * The parameter alias table
	 * @var array
	 */
	private static $parameterAliasTable = array(
		"breite" => "width",
		"höhe" => "height",
		"ausrichtung" => "alignment",
		"beschriftung" => "legend",
		"align" => "alignment",
		"typ" => "type",
		"rand" => "border",
		"datum" => "date",
		"src" => "url",
		"sprache" => "language"
	);

	/**
	 * Adds an parameter alias to the table.
	 * @param string $alias the alias parameter name
	 * @param string $parameter the real parameter name
	 */
	public static function addParameterAlias($alias, $parameter){
		self::$parameterAliasTable[$alias] = $parameter;
	}

	/**
	 * Applies the parameter alias table on the provided parameter name.
	 * @param string $parameter the parameter name to search for
	 * @return string Returns the real parameter name if an alias is found. Otherwise $parameter is returned unmodified
	 */
	public static function getRealParameterFor($parameter){
		if (array_key_exists($parameter, self::$parameterAliasTable)){
			return self::$parameterAliasTable[$parameter];
		}
		else {
			return $parameter;
		}
	}
}

?>
