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
	 * @param string|DBItemClassSpecifier $classSpecifier
	 * @param DBItemPlugin $plugin
	 */
	public static function addPluginCLASS($classSpecifier, DBItemPlugin $plugin){
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);
		if ($plugin->isValidClass($classSpecifier)){
			self::$plugins[] = array("class" => $classSpecifier, "plugin" => $plugin);
		}
		else {
			throw new InvalidArgumentException("Plugin '" . $plugin->getName() . "' is not build for class " . $classSpecifier . ".");
		}
	}
	
	/**
	 * Returns all plugins of a certain $class and all its parent classes.
	 * @param string|DBItemClassSpecifier $classSpecifier
	 * @return DBItemPlugin[]
	 */
	public static function getPluginsCLASS($classSpecifier){
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);
		$ret = array();
		foreach (self::$plugins as $plugin){
			if ($plugin["class"]->getClassName() === $classSpecifier->getClassName() || is_subclass_of($classSpecifier, $plugin["class"])){
				$ret[] = $plugin["plugin"];
			}
		}
		return $ret;
	}

}

?>
