<?php
/** @var DBItemFieldDBItem $this */
/** @var string $context */
/** @var array $args(
 *		"postName" => post name,
 *		"availableItems" => available items,
 *		"value" =>  current items) */
?>
			<input type="hidden" name="<?php echo $args["postName"];?>[present]" value="1">
				<?php
		$hmItems = $args["value"];
		foreach ($args["availableItems"] as $hmItem){
			echo "\n\t\t\t\t" .
				'<label class="editCheckboxLabel"><input type="checkbox" name="' . $args["postName"] . '[values][]" value="' . $hmItem->DBid . '"';
			if ($hmItems->contains($hmItem, true)){
				echo ' checked="checked"';
			}
			echo '>';
			$hmItem->view("checkboxLabel|singleLine", true);
			echo "</label>\n";
		}