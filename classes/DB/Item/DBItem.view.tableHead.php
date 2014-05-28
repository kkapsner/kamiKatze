<colgroup>
	<?php
	/* @var $this DBItemCollection */
	
	$fields = $args;
	foreach ($fields as $fieldItem){
		/* @var $fieldItem DBItemField */
		echo '<col class="' . $this->html($fieldItem->name) . '">';
	}
	?>
</colgroup>
<thead>
	<tr>
		<?php
		foreach ($fields as $fieldItem){
			/* @var $fieldItem DBItemField */
			echo "<th>" . $this->html($fieldItem->name) . "</th>";
		}
		?>
	</tr>
</thead>