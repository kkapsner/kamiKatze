<?php
/**
 * Viewable definition file
 */

/**
 * Interface for views.
 *
 * @author Korbinian Kapsner
 * @package Viewable
 */
interface Viewable{
	/**
	 * Invoces the view.
	 *
	 * @param string $context the context of the view
	 * @param bool $output if the produced text should be directly output.
	 * @param mixed $args an additional parameter
	 * @return string if output is disabled the text is returned.
	 */
	public function view($context = false, $output = false, $args = false);
}

?>
