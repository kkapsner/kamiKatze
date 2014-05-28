
			<input type="hidden" name="<?php echo $args["postName"];?>[present]" value="1">
<?php
/* @var $this DBItemFieldDBItem */
/* @var $context string */
/* @var $args DBItem */

$postName = $args["postName"];
echo "<ol>\n";
foreach ($args["value"] as $value){
	$this->view(
		"editField.multipleLinks.editRow",
		true,
		array(
			"postName" => $postName . "[values][]",
			"availableItems" => $args["availableItems"],
			"value" => $value
		)
	);
}
	?><li>
		<button type="button">create new</button>
		<script type="text/data"><?php
			$this->view(
				"editField.multipleLinks.editRow",
				true,
				array(
					"postName" => $postName . "[values][]",
					"availableItems" => $args["availableItems"],
					"value" => DBItem::getCLASS($this->class, 0)
				)
			);
		?></script>
		<script type="text/javascript">
			(function(){
				var sc = document.getElementsByTagName("script");
				sc = sc[sc.length - 2];
				var button = document.getElementsByTagName("button");
				button = button[button.length - 1];
				var newID = 0;
				button.onclick = function(){
					var div = document.createElement("div");
					var html = sc.text.replace(/(?:\<|&lt;)new(?:\>|&gt;)/g, newID);
					newID++;
					div.innerHTML = "<ol>" + html + "</ol>";
					button.parentNode.parentNode.insertBefore(
						div.getElementsByTagName("li")[0],
						button.parentNode
					);
				}
			})();
		</script>
	</li>
</ol>