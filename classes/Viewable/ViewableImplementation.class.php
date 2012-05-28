<?php

/**
 * Description of ViewableImplementation
 *
 * @author kkapsner
 */
class ViewableImplementation implements Viewable{
	
	public function viewByName($name, $context = false, $output = false, $args = false){
		$al = Autoload::getInstance();
		$path = $al->getLoadingPoint($name);
		if ($path === false){
			$reflection = new ReflectionClass($name);
			$path = $reflection->getFileName();
		}
		$file = dirname($path) . DIRECTORY_SEPARATOR .
			$name . ".view" .
			($context? "." . $context: "") .
			".php";
		if (!is_file($file)){
			$parent = get_parent_class($name);
			if ($parent !== false){
				return $this->viewByName($parent, $context, $output);
			}
			else {
				return false;
			}
		}
		else {
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
	}
	
	public function view($context = false, $output = false, $args = false){
		return $this->viewByName(get_class($this), $context, $output, $args);
	}
}

?>
