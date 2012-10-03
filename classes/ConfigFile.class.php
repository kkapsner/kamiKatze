<?php
/**
 * ConfigFile definition file
 */

/**
 * Class to read and parse config files.
 *
 * @author Korbinian Kapsner
 */
class ConfigFile{
	/**
	 * Storage for the found variables.
	 * @var mixed[]
	 */
	protected $variables = array();

	/**
	 * Flag for forcing camel cased variable names.
	 * @var bool
	 */
	protected $camelCase = false;

	/**
	 * The filename
	 * @var string
	 */
	protected $filename;

	/**
	 * Constructor for ConfigFile
	 *
	 * @param string $filename
	 */
	public function __construct($filename){
		$this->filename = basename($filename);
	}

	/**
	 * Loads the config file and parses the content.
	 *
	 * @param string $path The path to the config file. If not present the config file is searched with the Autoload::searchFile() function.
	 * @throws ConfigFileNotFoundException
	 */
	public function load($path = false){
		if ($path === false){
			$path = Autoload::getInstance()->searchFile($this->filename);
			if ($path === false){
				throw new ConfigFileNotFoundException("Config File '" . $this->filename . "' not found.");
			}
			else {
				$content = file($path, FILE_IGNORE_NEW_LINES);
			}
		}
		else {
			$path = realpath($path) . DIRECTORY_SEPARATOR . $this->filename;
			if (!is_file($file)){
				throw new ConfigFileNotFoundException("Config File '" . $this->filename . "' not found.");
			}
			else {
				$content = file($file, FILE_IGNORE_NEW_LINES);
			}
		}
			
		$praefix = array();
		for ($i = 0; $i < count($content); $i++){
			$line = $content[$i];
			
			# remove #-comments
			$line = trim(preg_replace('/("(?:\\\\.|[^"])*"|\'(?:\\\\.|[^\'])*\')|#.*$/', "$1", $line));
			
			if (strlen($line) !== 0){
				$openBracket = strrpos($line, "{");
				$closeBracket = strrpos($line, "}");
				
				#take only smaller one
				if ($openBracket !== false){
					if ($closeBracket !== false){
						if ($openBracket > $closeBracket){
							$openBracket = false;
						}
						else {
							$closeBracket = false;
						}
					}
				}
				
				if ($openBracket !== false){
					array_splice($content, $i + 1, 0, substr($line, $openBracket + 1));
					$line = trim(substr($line, 0, $openBracket));
				}
				
				if ($closeBracket !== false){
					array_splice($content, $i + 1, 0, substr($line, $closeBracket + 1));
					$line = trim(substr($line, 0, $closeBracket));
				}
				
				if (strlen($line) !== 0){
					if ($openBracket === false){
						$parts = explode('=', $line, 2);
						$name = preg_replace("/[_-]+([_-])/", '$1', (count($praefix)? join("_", $praefix) . "_": "") . trim($parts[0]));
						$value = (count($parts) >= 2)? trim($parts[1]): "";
						$this->__set($name, $this->parseValue($value));
					}
					else {
						array_push($praefix, $line);
					}
				}
				
				if ($closeBracket !== false){
					array_pop($praefix);
				}
			}
		}
		
	}
	
	/**
	 * Setter for camelCase
	 * @param bool $cc
	 */
	public function setCamelCase($cc){
		$this->camelCase = $cc;
	}

	/**
	 * Getter for camelCase
	 * @return bool
	 */
	public function getCamelCase(){
		return $this->camelCase;
	}

	/**
	 * Parses a given variable name to the desired format
	 *
	 * @see ConfigFile::camelCase
	 * @param string $name
	 * @return string
	 */
	private function parseName($name){
		if ($this->camelCase){
			return $this->toCamelCase($name);
		}
		else {
			return $name;
		}
	}

	/**
	 * Translates a string to camel case.
	 *
	 * @param string $str
	 * @return string
	 */
	public function toCamelCase($str){
		if (is_array($str)){
			return ucfirst($str[1]);
		}
		elseif (is_string($str)){
			return preg_replace_callback("/[_-]+([^_-]*)/", array($this, "toCamelCase"), preg_match("/^[A-Z_]+$/", $str)? strToLower($str): $str);
		}
		else {
			return "";
		}
	}
	
	/**
	 * Parses a value string. This is done with eval...
	 *
	 * @param string $value
	 * @return mixed
	 * @todo avoid eval
	 */
	private function parseValue($value){
		$value = preg_replace_callback('/<((?:[^<>]|\\.)*)>|{((?:[^{}]|\\.)*)}/', array($this, "replaceVariableInValue"), $value);
		return eval("return " . $value . ";");
	}

	/**
	 * RegExp callback for ConfigFile::parseValue(). DO NOT USE.
	 *
	 * @param string[] $m
	 * @return mixed
	 * @todo better implementation to hide this function (or remove it)
	 */
	public function replaceVariableInValue($m){
		return $this->valueToCode($this->__get($m[0] . $m[1]));
	}

	/**
	 * Generates PHP-code from a value
	 *
	 * @param mixed $value
	 * @return string
	 * @todo is this function really neccessary? if we switch to JSON-encoding...
	 */
	public function valueToCode($value){
		if (is_string($value)){
			return '"' . str_replace('"', '\"', str_replace('\\', '\\\\', $value)) . '"';
		}
		elseif (is_array($value)){
			return "array(" . implode(",", array_map(array($this, "valueToCode"), $value)) . ")";
		}
		elseif (is_boolean($value)){
			return $value? "true": "false";
		}
		elseif (is_numeric($value)){
			return $value;
		}
		else {
			return "''";
		}
	}

	/**
	 * Magic __set method.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value){
		$this->variables[$this->parseName($name)] = $value;
	}

	/**
	 * Magic __get method.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		$name = $this->parseName($name);
		return (array_key_exists($name, $this->variables))? $this->variables[$name]: NULL;
	}
}

/**
 * Exception if a config file was not found.
 */
class ConfigFileNotFoundException extends Exception{}

?>