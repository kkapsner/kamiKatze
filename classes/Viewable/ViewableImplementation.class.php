<?php
/**
 * ViewableImplementation definition file
 */

/**
 * Specific implementation of the Viewable interface.
 *
 * @author Korbinian Kapsner
 * @package Viewable
 */
class ViewableImplementation extends EventEmitterImplementation implements Viewable{

	/**
	 * Similar to Viewable::view() but ability to provide a class name to
	 *  specify the view which should be used.
	 *
	 * @param string $name class name to be used
	 * @param string $context
	 * @param boolean $output
	 * @param mixed $args
	 * @return string|mixed
	 */
	public function viewByName($name, $context = false, $output = false, $args = false){
		$file = $this->getViewFile($name, $context);
		if ($file){
			if (!$output){
				ob_start();
			}
			$ret = include($file);
			if (!$output){
				$contents = ob_get_contents();
				ob_end_clean();
				return $contents;
			}
			else { 
				return $ret;
			}
		}
		else {
			return false;
		}
	}
	
	/**
	 * Gets the path to the view file specified by the class name and the
	 * context. The context can be pipe separated to indicate fallbacks. First
	 * all parent classes of a class are checked before the next fallback is
	 * used.
	 * 
	 * @param string $name The class name
	 * @param string $context The context of the view
	 * @return string|boolean Returns the path to the right view file on success
	 *	or false on failure.
	 */
	protected function getViewFile($name, $context){
		$al = Autoload::getInstance();
		$contextChain = explode("|", $context? $context: "");
		foreach ($contextChain as $currentContext){
			$currentName = $name;
			$reflection = new ReflectionClass($currentName);
			while ($reflection){
				$currentName = $reflection->getName();
				$path = $al->getLoadingPoint($currentName);
				if ($path === false){
					$path = $reflection->getFileName();
				}
				$file = dirname($path) . DIRECTORY_SEPARATOR .
					$reflection->getShortName() . ".view" .
					($currentContext? "." . $currentContext: "") .
					".php";
				if (is_file($file)){
					return $file;
				}
				else {
					$reflection = $reflection->getParentClass();
				}
			}
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $context
	 * @param boolean $output
	 * @param mixed $args
	 * @return string|boolean
	 */
	public function view($context = false, $output = false, $args = false){
		return $this->viewByName(get_class($this), $context, $output, $args);
	}
}

?>
