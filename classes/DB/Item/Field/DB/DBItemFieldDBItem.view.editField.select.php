<?php
/* @var $this DBItemFieldDBItem */
/* @var $context string */
/* @var $args array(
 *		"postName" => post name,
 *		"availableItems" => available items,
 *		"value" =>  current item) */
?>
			<select name="<?php echo $args["postName"];?>">
				<?php
		if ($this->null){
			echo "<option></option>";
		}
		$value = $args["value"];
		foreach ($args["availableItems"] as $hoItem){
			?>
				<option value="<?php
			echo $hoItem->DBid;
			if ($value === $hoItem){ echo '" selected="selected';}
		?>"><?php $hoItem->view("singleLine", true)?></option>
					<?php
		}
		?>
			</select>