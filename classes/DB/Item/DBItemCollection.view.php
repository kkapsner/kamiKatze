<table class="collection <?php echo $this->getClass();?>">
	<?php
	$this->viewByName($this->getClass(), "tableHead", true);
	?>
	<tbody>
		<?php
		if ($this->count()){
			foreach ($this as $item){
				$item->view("tableRow", true);
			}
		}
		else {
			echo '<td colspan="' . count($fields) . '"><em>empty</em></td>';
		}
		?>
	</tbody>
</table>