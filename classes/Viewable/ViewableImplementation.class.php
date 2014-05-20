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
	 * Similar to Viewable::view() but ability to provide a class name to specify the view which should be used.
	 *
	 * @param string $name class name to be used
	 * @param string $context
	 * @param boolean $output
	 * @param mixed $args
	 * @return string|boolean
	 */
	public function viewByName($name, $context = false, $output = false, $args = false){
		$file = $this->getViewFile($name, $context);
		if ($file){
			if (!$output){
				ob_start();
			}
			include($file);
			if (!$output){
				$contents = ob_get_contents();
				ob_end_clean();
				return $contents;
			}
			else { 
				return true;
			}
		}
		else {
			return false;
		}
	}
	
	protected function getViewFile($name, $context){
		$al = Autoload::getInstance();
		$contextChain = explode("|", $context? $context: "");
		foreach ($contextChain as $currentContext){
			$currentName = $name;
			while ($currentName){
				$path = $al->getLoadingPoint($currentName);
				if ($path === false){
					$reflection = new ReflectionClass($currentName);
					$path = $reflection->getFileName();
				}
				$file = dirname($path) . DIRECTORY_SEPARATOR .
					$currentName . ".view" .
					($currentContext? "." . $currentContext: "") .
					".php";
				if (is_file($file)){
					return $file;
				}
				else {
					$currentName = get_parent_class($currentName);
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
