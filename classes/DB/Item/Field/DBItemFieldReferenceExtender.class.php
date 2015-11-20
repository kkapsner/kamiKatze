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
class DBItemFieldReferenceExtender extends DBItemFieldReferenceEnum implements DBItemFieldHasSearchableSubcollection{
	use DBItemFieldExtenderTrait;
	
	public function getDefault(){
		return $this->idToValue($this->default);
	}
}