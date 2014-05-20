<tr>
	<?php
	
	/* @var $this DBItem */
	foreach (DBItemField::parseClass(get_class($this)) as $fieldItem){
		/* @var $fieldItem DBItemFieldOption */
		echo '<td class="' . $this->html($fieldItem->name) . '">';
		$fieldItem->view(false, true, $this);
		echo "</td>";
	}
	?>
</tr>