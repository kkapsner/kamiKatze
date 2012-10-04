<?php
/**
 * DBItemPlugable definition file
 */

/**
 * A DB item that can have plugins.
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
abstract class DBItemPlugable extends DBItem{
	/**
	 * Stores all pluginss for ALL DBItems
	 * @var DBItemPlugin[]
	 */
	protected static $plugins = array();

	/**
	 * Adds an plugin to a certain $class.
	 * @param string $class
	 * @param DBItemPlugin $plugin
	 */
	public static function addPluginCLASS($class, DBItemPlugin $plugin){
		if ($plugin->isValidClass($class)){
			self::$plugins[] = array("class" => $class, "plugin" => $plugin);
		}
		else {
			throw new InvalidArgumentException("Plugin '" . $plugin->getName() . "' is not build for class " . $class . ".");
		}
	}
	
	/**
	 * Returns all plugins of a certain $class and all its parent classes.
	 * @param string $class
	 * @return DBItemPlugin[]
	 */
	public static function getPluginsCLASS($class){
		$ret = array();
		foreach (self::$plugins as $plugin){
			if ($plugin["class"] === $class || is_subclass_of($class, $plugin["class"])){
				$ret[] = $plugin["plugin"];
			}
		}
		return $ret;
	}

}

?>
