<?php
/* @var $this DBItemFieldDBItem */
/* @var $context string */
/* @var $args array(
 *		"postName" => post name,
 *		"groups" => available items in groups,
 *		"value" =>  current items) */
?>
			<input type="hidden" name="<?php echo $args["postName"];?>[present]" value="1">
			<select name="<?php echo $args["postName"];?>[values][]" multiple="multiple">
				<?php
		$hmItems = $args["value"];
		$i = 0;
		foreach ($args["groups"] as $groupName => $group){
			if ($groupName && count($args["groups"]) > 1){
				echo "<optgroup label=\"" . $this->html($groupName) . "\">";

			}
			
			foreach ($group as $hmItem){
				?>
				<option value="<?php
				echo get_class($hmItem) . "#" . $hmItem->DBid;
				if ($hmItems[$i]->contains($hmItem)){ echo '" selected="selected';}
			?>"><?php $hmItem->view("singleLine", true)?></option>
					<?php
			}
			if ($groupName){
				echo "</optgroup>";
			}
			$i += 1;
		}
		?>
			</select>