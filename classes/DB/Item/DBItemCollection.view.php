<table class="collection <?php echo $this->getClass();?>">
	<colgroup>
		<?php
		/* @var $this DBItemCollection */
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
	<tbody>
		<?php
		if ($this->count()){
			foreach ($this as $item){
				/* @var $item DBItem */
				echo '<tr>';
				foreach ($fields as $fieldItem){
					/* @var $fieldItem DBItemFieldOption */
					echo '<td class="' . $this->html($fieldItem->name) . '">';
					$fieldItem->view(false, true, $item);
					echo "</td>";
				}
				echo '</tr>';
			}
		}
		else {
			echo '<td colspan="' . count($fields) . '"><em>empty</em></td>';
		}
		?>
	</tbody>
</table>