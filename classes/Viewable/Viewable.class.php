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
	 * @return string|mixed if output is disabled the text is returned otherwise
	 *	the return value of the view-file is passed through
.	 */
	public function view($context = false, $output = false, $args = false);
}

?>
