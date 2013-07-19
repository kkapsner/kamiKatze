<!DOCTYPE html>
<html>
<head>
	<meta charset="<?php echo $this->charset;?>">
	<!--<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->charset;?>">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">-->
<?php
	/* @var $this Template */
	if ($this->favicon) {
?>
	<link href="<?php echo $this->url($this->stylePlace . $this->favicon);?>" type="image/x-icon" rel="icon">
	<link href="<?php echo $this->url($this->stylePlace . $this->favicon);?>" type="image/x-icon" rel="shortcut icon">
<?php
	}
?>
	<title><?php echo $this->html($this->title);?></title>
<?php
	foreach ($this->meta as $k => $v){?>
	<meta name="<?php echo $this->html($k)?>" content="<?php echo $this->html($v)?>">
<?php
	}
	foreach ($this->style as $style){
		$style->view($context, true);
	}
	foreach ($this->script as $script){
		$script->view($context, true);
	}
	foreach ($this->headTags as $headTag){
		$headTag->view($context, true);
	}
	?>
</head>
<?php
	if (count($this->lateScript)){
		$body = $this->view($context, false);
		$injectedHTML = "";
		foreach ($this->lateScript as $script){
			$injectedHTML .= $script->view($context, false);
		}
		echo str_replace("</body>", $injectedHTML . "</body>", $body);
	}
	else {
		$this->view($context, true);
	}
	
?>
</html>