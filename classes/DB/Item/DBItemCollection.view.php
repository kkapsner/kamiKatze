<table class="collection <?php echo $this->getClass();?>">
	<?php
	$allFields = DBItemField::parseClass($this->getClass());
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
	
	$this->viewByName($this->getClass(), "tableHead", true, $fields);
	?>
	<tbody>
		<?php
		if ($this->count()){
			foreach ($this as $item){
				$item->view("tableRow", true, $fields);
			}
		}
		else {
			echo '<td colspan="' . count($fields) . '"><em>empty</em></td>';
		}
		?>
	</tbody>
</table>