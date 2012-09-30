<table class="collection <?php echo $this->getClass();?>">
	<colgroup>
		<?php
		/* @var $this DBItemCollection */
		$fieldOptions = DBItemFieldOption::parseClass($this->getClass());
		foreach ($fieldOptions as $fieldItem){
			/* @var $fieldItem DBItemFieldOption */
			echo '<col class="' . $fieldItem->name . '">';
		}
		?>
	</colgroup>
	<thead>
		<tr>
			<?php
			foreach ($fieldOptions as $fieldItem){
				/* @var $fieldItem DBItemFieldOption */
				echo "<th>" . $fieldItem->name . "</th>";
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
				foreach ($fieldOptions as $fieldItem){
					/* @var $fieldItem DBItemFieldOption */
					echo '<td class="' . $fieldItem->name . '">';
					$fieldItem->view(false, true, $item);
					echo "</td>";
				}
				echo '</tr>';
			}
		}
		else {
			echo '<td colspan="' . count($fieldOptions) . '"><em>empty</em></td>';
		}
		?>
	</tbody>
</table>