<colgroup>
	<?php
	/** @var DBItemCollection $this */
	
	$fields = $args;
	foreach ($fields as $fieldItem){
		/** @var DBItemField $fieldItem */
		if ($fieldItem->displayable){
			echo '<col class="' . $this->html($fieldItem->name) . '">';
		}
	}
	?>
</colgroup>
<thead>
	<tr>
		<?php
		foreach ($fields as $fieldItem){
			/** @var DBItemField $fieldItem */
			if ($fieldItem->displayable){
				echo "<th>" . $this->html($fieldItem->displayName) . "</th>";
			}
		}
		?>
	</tr>
</thead>