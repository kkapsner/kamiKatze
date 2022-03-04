<tr>
	<?php
	
	/** @var DBItem $this */
	foreach ($args as $fieldItem){
		/** @var DBItemFieldOption $fieldItem */
		if ($fieldItem->displayable){
			echo '<td class="' . $this->html($fieldItem->name) . '">';
			$fieldItem->view(false, true, $this);
			echo "</td>";
		}
	}
	?>
</tr>