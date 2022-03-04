<?php

/** @var ViewableHTMLTag $this */
echo "<" . $this->tagName;
foreach ($this->getHTMLAttirbuteList() as $name){
	echo $this->getHTMLAttribute($name, true);
}
echo ">";

if ($this->content){
	$this->content->view($content, true);
}

echo "</" . $this->tagName . ">";
?>