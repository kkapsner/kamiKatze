<tr>
<?php
/** @var DBItemFieldCollection $this */
/** @var DBItem $args */

foreach ($this as $field){
	if ($field->displayable){
		echo "\n\t" . '<td>';
		$field->view(false, true, $args);
		echo '</td>';
	}
}

?>
</tr>