<?php
/* @var $this DBItemFieldDBItem */
/* @var $context string */
/* @var $args array(
 *		"postName" => post name,
 *		"groups" => available items in groups,
 *		"value" =>  current item) */
?>
			<select name="<?php echo $args["postName"];?>">
				<option></option><?php
		$value = $args["value"];
		foreach ($args["groups"] as $groupName => $group){
			if ($groupName){
				echo "<optgroup label=\"" . $this->html($groupName) . "\">";

			}

			foreach ($group as $hoItem){
				?>
				<option value="<?php
				echo get_class($hoItem) . "#" . $hoItem->DBid;
				if ($value === $hoItem){ echo '" selected="selected';}
			?>"><?php $hoItem->view("singleLine", true)?></option>
					<?php
			}
			if ($groupName){
				echo "</optgroup>";
			}
		}
		?>
			</select>