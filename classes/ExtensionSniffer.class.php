<?php

/**
 * Description of ExtensionSniffer
 *
 * @author kkapsner
 */
class ExtensionSniffer{
	/**
	 * The mapping of the extensions to the MIME-types.
	 * @var array
	 */
	private static $extensionsMapping = array();
	/**
	 * File name of the extensions file.
	 * @var string
	 */
	private static $extensionsFile = "ExtensionSniffer-mimeType.txt";
	/**
	 * If the sniffer is initialised.
	 * @var bool
	 */
	private static $initialised = false;
	/**
	 * Initialises the sniffer.
	 */
	public static function init(){
		if (!self::$initialised){
			$lines = file(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$extensionsFile);
			foreach ($lines as $line){
				$line = trim($line);
				if ($line[0] !== "#"){
					$p = preg_split('/\s+/', $line, 2);
					if (count($p) === 2){
						if (!array_key_exists($p[0], self::$extensionsMapping)){
							self::$extensionsMapping[$p[0]] = array();
						}
						self::$extensionsMapping[$p[0]][] = $p[1];
					}
				}
			}
		}
	}

	/**
	 * Returns the MIME-types which correspond to the given file-extension.
	 * @param string $extension
	 * @parame string $default the value returned if the extension is not recognised.
	 * @return string
	 */
	public static function extensionToMime($extension, $default = "application/octet-stream"){
		if (array_key_exists($extension, self::$extensionsMapping)){
			return self::$extensionsMapping[$extension][0];
		}
		return $default;
	}
	
	/**
	 * Returns the MIME-types which correspond to the given filename.
	 * @param string $filename
	 * @parame string $default the value returned if the extension is not recognised.
	 * @return string
	 */
	public static function filenameToMime($filename, $default = "application/octet-stream"){
		$pos = strrpos(".", $filename);
		if ($pos !== false){
			return self::extensionToMime(substr($filenmae, $pos + 1), $default);
		}
		return null;
	}
}

ExtensionSniffer::init();
?>
