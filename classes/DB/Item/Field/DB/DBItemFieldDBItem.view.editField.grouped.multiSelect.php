<?php
/** @var DBItemFieldDBItem $this */
/** @var string $context */
/** @var array $args(
 *		"postName" => post name,
 *		"groups" => available items in groups,
 *		"value" =>  current items) */

$groups = array();
$values = array();
$removedGroup = false;
$i = 0;
foreach ($args["groups"] as $groupName => $group){
	if (count($group) > 0){
		$groups[$groupName] = $group;
		$values[] = $args["value"][$i];
	}
	else {
		$removedGroup = true;
	}
	$i += 1;
}

if (!$removedGroup && count($groups) === 1){
	$args["availableItems"] = array_values($groups)[0];
	$args["value"] = $args["value"][0];
	$this->viewByName(
		"DBItemFieldDBItem",
		"editField.multiSelect",
		true,
		$args
	);
}
else {
?>
			<input type="hidden" name="<?php echo $args["postName"];?>[present]" value="1">
			<select name="<?php echo $args["postName"];?>[values][]" multiple="multiple">
				<?php
		$hmItems = $args["value"];
		$i = 0;
		foreach ($groups as $groupName => $group){
			if ($groupName){
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
			</select><?php
}?>