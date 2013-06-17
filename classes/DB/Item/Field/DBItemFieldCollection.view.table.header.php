<thead>
	<tr>
<?php
/* @var $this DBItemFieldCollection */

foreach ($this as $field){
	if ($field->displayable){
		echo "\n\t\t" . '<th>' . $this->html($field->displayName) . '</th>';
	}
}
if (is_array($args)){
	foreach ($args as $col){
		echo "\n\t\t" . '<th>' . $this->html($col) . '</th>';
	}
}
?>
	</tr>
</thead>