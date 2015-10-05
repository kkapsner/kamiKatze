<div class="collection <?php echo $this->html($this->getClass() . " "  . implode(" ", explode("|", $context)));?>"><?php

/* @var $this DBItemCollection */

foreach ($this as $item){
	/* @var $item DBItem */
	$item->view($context, true, $args);
}

?>
</div>