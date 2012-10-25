<tr>
<?php
/* @var $this DBItemFieldCollection */
/* @var $args DBItem */

foreach ($this as $field){
	if ($field->displayable){
		echo "\n\t" . '<td>';
		$field->view(false, true, $args);
		echo '</td>';
	}
}

?>
</tr>