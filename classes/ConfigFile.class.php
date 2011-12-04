<?php

class ConfigFile{
	protected
		$variables = array(),
		$camelCase = false,
		$filename
	;
	
	public function __construct($filename){
		$this->filename = basename($filename);
	}
	
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
	
	
	public function setCamelCase($cc){
		$this->camelCase = $cc;
	}
	public function getCamelCase(){
		return $this->camelCase;
	}
	
	private function parseName($name){
		if ($this->camelCase) return $this->toCamelCase($name);
		else return $name;
	}
	
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
	
	
	private function parseValue($value){
		$value = preg_replace_callback('/<((?:[^<>]|\\.)*)>|{((?:[^{}]|\\.)*)}/', array($this, "replaceVariableInValue"), $value);
		return eval("return " . $value . ";");
	}
	
	public function replaceVariableInValue($m){
		return $this->valueToCode($this->__get($m[0] . $m[1]));
	}
	
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
	
	public function __set($name, $value){
		$this->variables[$this->parseName($name)] = $value;
	}
	
	public function __get($name){
		$name = $this->parseName($name);
		return (array_key_exists($name, $this->variables))? $this->variables[$name]: NULL;
	}
}

class ConfigFileNotFoundException extends Exception{}

?>