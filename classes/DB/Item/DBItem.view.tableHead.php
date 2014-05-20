<colgroup>
	<?php
	/* @var $this DBItemCollection */
	$allFields = DBItemField::parseClass($name);
	if ($args){
		$fields = new DBItemFieldCollection();
		foreach ($allFields as $fieldItem){
			if (in_array($fieldItem->name, $args)){
				$fields[] = $fieldItem;
			}
		}
	}
	else {
		$fields = $allFields;
	}
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