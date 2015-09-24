<?php
/* @var $this DBItemFieldArray */
/* @var $context string */
/* @var $args mixed[] Array of the postName and the DBItem the sub item!*/

echo "<tr>";
$subID = $args[1]->DBid === 0? "<new>": $args[1]->DBid;
$this->currentSubID = $subID;
foreach ($this->arrayFields as $field){
	$oldParent = $field->parentField;
	$field->parentField = $this;
	
	/* @var $field DBItemField */
	if ($field !== $this->linkField && $field->displayable){
		echo '<td>';
		$field->view("editField", true, $args[1]);
		echo '</td>';
	}
	
	$field->parentField = $oldParent;
}
$this->currentSubID = null;
if ($args[1]->DBid !== 0){
	echo '<td><input type="hidden" name="' . $args[0] . '[' . $subID . '][data][id]" value="' . $subID . '">';
	echo '<input type="checkbox" name="' . $args[0] . '[' . $subID . '][action]" value="delete"></td>';
	echo "</tr>";
}
else {
	
	echo '<td><input type="hidden" name="' . $args[0] . '[' . $subID . '][action]" value="create">';
	echo '<button type="button" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);">delete</button></td>';
	echo "</tr>";
}
?> 