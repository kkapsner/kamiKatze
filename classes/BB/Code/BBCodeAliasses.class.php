<?php

/**
 * @author kkapsner
 */
class BBCodeAliasses{

	private function __construct(){

	}

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

	public static function addTagAlias($alias, $tag){
		self::$tagAliasTable[$alias] = $tag;
	}

	public static function getRealTagFor($tagName){
		if (array_key_exists($tagName, self::$tagAliasTable)){
			return self::$tagAliasTable[$tagName];
		}
		else {
			return $tagName;
		}
	}

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

	public static function addParameterAlias($alias, $parameter){
		self::$parameterAliasTable[$alias] = $parameter;
	}

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
