<?php
/**
 * DBItemFieldExtender definition file
 */

/**
 * Representation of an enum extender field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldExtender extends DBItemFieldEnum implements DBItemFieldHasSearchableSubcollection{
	use DBItemFieldExtenderTrait;
	
	public function getDefault(){
		return $this->default;
	}
}