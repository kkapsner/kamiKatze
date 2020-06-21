<colgroup>
	<?php
	/* @var $this DBItemCollection */
	
	$fields = $args;
	foreach ($fields as $fieldItem){
		/* @var $fieldItem DBItemField */
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
			/* @var $fieldItem DBItemField */
			if ($fieldItem->displayable){
				echo "<th>" . $this->html($fieldItem->displayName) . "</th>";
			}
		}
		?>
	</tr>
</thead>