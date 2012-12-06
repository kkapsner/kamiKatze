<table class="collection <?php echo $this->getClass();?>">
	<colgroup>
		<?php
		/* @var $this DBItemCollection */
		$fields = DBItemField::parseClass($this->getClass());
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