<?php

/* @var $this ViewableHTMLTagScript */
echo "<" . $this->tagName;
foreach ($this->getHTMLAttirbuteList() as $name){
	echo $this->getHTMLAttribute($name, true);
}
echo ">";

if ($this->code){
	echo str_replace("</script", '<\/script', $this->code);
}

echo "</" . $this->tagName . ">";
?>