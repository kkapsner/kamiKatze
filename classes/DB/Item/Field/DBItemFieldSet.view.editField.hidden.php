<?php
/** @var DBItemFieldOption $this */
/** @var string $context */
/** @var DBItem $args */

$postName = $this->getPostName($args);

$eventBase = "view.editField." . $this->name;

$args->emit($eventBase . ".beforeHidden");
echo "\n\t\t\t" . '<input type="hidden" name="' . $postName . '[present]" value="1" >';
$args->emit($eventBase . ".afterHidden");

?>