<?php

class Autoload {
	/**
	 * file extension for a class file
	 * @var string
	 */
	public $classExtension = ".class.php";
	/**
	 * file extension for a view file
	 * @var string
	 */
	public $viewExtension = ".view.php";
	protected $searchPath = array();
	protected function __construct(){
		$mainScriptDir = realpath(dirname($_SERVER["SCRIPT_FILENAME"])) . DIRECTORY_SEPARATOR;
		$this->addPath($mainScriptDir);
		$this->addPath($mainScriptDir . "classes");
		$this->addPath((dirname(__FILE__)) . DIRECTORY_SEPARATOR . "classes");
	}

	/**
	 * The one and only instance.
	 * @var Autoload
	 */
	protected static $instance = null;
	/**
	 * Factory method.
	 * @return Autoload
	 */
	public static function getInstance(){
		if (self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Adds a path to the search array. Checks if the path is correct, points to a directory and if it is not already in the array.
	 * @param string $path
	 * @return bool if the path was added
	 */
	public function addPath($path){
		$path = realpath($path);
		if ($path !== false && is_dir($path) && !in_array($path, $this->searchPath)){
			$this->searchPath[] = $path . DIRECTORY_SEPARATOR;
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Removes a path form the search array.
	 * @param string $path
	 */
	public function removePath($path){
		$path = realpath($path) . DIRECTORY_SEPARATOR;
		$pos = array_search($path, $this->searchPath);
		if ($pos !== false){
			array_splice($this->searchPath, $pos, 1);
		}
	}

	/**
	 * Searches for a file in the search array paths and returns the found path or false on failure.
	 * @param type $subpath
	 * @return mixed the found path or false.
	 */
	public function searchFile($subpath){
		foreach ($this->searchPath as $sPath){
			if (is_file($sPath . $subpath)){
				return $sPath . $subpath;
			}
		}
		return false;
	}
	
	/**
	 * Splits a classname into an array. The splits are done at an underscore ("_") or at camelcase positions.
	 * The underscores are removed and subsequential underscores are counted as one. Beginning and trailing underscores are ignored.
	 * Example (the pipe ("|") marks the splits - brackets mark ignored characters): (_)This|IS|_(_)A|Test(_)
	 * @param string $classname
	 * @return array splitted classname 
	 */
	public function splitClassname($classname){
		$len = strlen($classname);
		$ret = array();
		$currentPart = "";
		for ($i = 0; $i < $len; $i++){
			$char = $classname[$i];
			if ($char === "_"){
				if ($currentPart !== ""){
					$ret[] = $currentPart;
					$currentPart = "";
				}
			}
			elseif (ctype_upper($char)){
				if (
					$currentPart !== "" &&
					(
						!ctype_upper($currentPart) ||
						(
							$i + 1 < $len &&
							!ctype_upper($classname[$i + 1]) &&
							$classname[$i + 1] !== "_"
						)
					)
				){
					$ret[] = $currentPart;
					$currentPart = "";
				}
				$currentPart .= $char;
			}
			else {
				$currentPart .= $char;
			}
		}
		if ($currentPart !== ""){
			$ret[] = $currentPart;
		}
		return $ret;
	}

	/**
	 * The paths to the class files.
	 * @var array
	 */
	public $loadingPoints = array();
	/**
	 *
	 * @param string $classname
	 * @param string $namespace 
	 */
	public function load($classname, $namespace = ""){
		$split = array_values(
			array_filter(explode("\\", $namespace . "\\" . $classname))
		);
		$namespace = implode(DIRECTORY_SEPARATOR, array_slice($split, 0, -1)) . DIRECTORY_SEPARATOR;
		$classname = $split[count($split) - 1];
		#search classfile direct in search paths
		$iPath = $this->searchFile($namespace . $classname . $this->classExtension);
		if ($iPath === false){
			#search classfile in subfolder
			foreach ($this->splitClassname($classname) as $part){
				$namespace .= $part . DIRECTORY_SEPARATOR;
				#echo $namespace . $classname. $this->classExtension . "\n"; #for Debug only
				$iPath = $this->searchFile($namespace . $classname. $this->classExtension);
				if ($iPath){
					break;
				}
			}
		}
		if ($iPath !== false){
			include_once($iPath);
			if (!array_key_exists($namespace, $this->loadingPoints)){
				$this->loadingPoints[$namespace] = array();
			}
			$this->loadingPoints[$namespace][$classname] = $iPath;
		}
	}
}

?>