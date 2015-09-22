<article><?php
/* @var $this DBItemFieldDBDynamicItem */
/* @var $context string */
/* @var $args DBItem */

$postName = $this->getPostName($args);
$value = $this->getValue($args);

$this->classField->view("editField", true, $args);
$this->class = $this->classField->getValue($args);

foreach ($this->classField->typeExtension as $class){
	$availableItems = DBItem::getByConditionCLASS($class);
	echo "<div class=\"itemSelect $class\">";
	$this->view(
		"editField.select",
		true,
		array(
			"postName" => $postName . "[" . $class . "]",
			"availableItems" => $availableItems,
			"value" => $this->getValue($args)
		)
	);
	echo "</div>";
}
?>
	<script>
	(function(){
		function adjustDisplay(){
			var className = this.value;
			itemSelects.forEach(function(select){
				select.style.display =
					select.className.match(new RegExp("\\b" + className + "\\b"))?
					"":
					"none";
			});
		}
		var article = document.getElementsByTagName("article");
		article = article[article.length - 1];
		if (article){
			var classSelect = article.getElementsByTagName("select")[0];
			var itemSelects = Array.prototype.slice.call(article.getElementsByClassName("itemSelect"));
			if (classSelect){
				classSelect.addEventListener("change", adjustDisplay, false);
				adjustDisplay.call(classSelect);
			}
		}
	}());
	</script>
</article>