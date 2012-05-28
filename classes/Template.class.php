<?php

abstract class Template extends ViewableHTML{
	public $title,
		$style = array(), $stylePlace,
		$script = array(), $scriptPlace,
		$headTags = array(),
		$meta = array(),
		$favicon = "",
		$navigationClasses = array("li" => "navigationItem", "a" => "navigationLink", "ulSub" => "subnavigation");
	
	public function addStyle($name, $addSTYLE = true){
		if (!is_a($name, "ViewableHTMLTagStyle")){
			$style = new ViewableHTMLTagStyle();
			$style->setHTMLAttribute("src", ($addSTYLE? $this->stylePlace: "") . $name);
		}
		else {
			$style = $name;
		}
		$this->style[] = $style;
		return $style;
	}
	public function addScript($name, $addSCRIPT = true){
		if (!is_a($name, "ViewableHTMLTagScript")){
			$script = new ViewableHTMLTagScript();
			$script->setHTMLAttribute("src", ($addSCRIPT? $this->scriptPlace: "") . $name, true);
		}
		else {
			$script = $name;
		}
		array_push($this->script, $script);
		return $script;
	}
	public function addHeadTag(HeadTag $tag){
		$this->headTags[] = $tag;
	}
	
	protected function getErrorLi(){
		global $error;
		global $message;
		
		$ret = array('<li style="display: none;"></li>', 
'<!--[if lte IE 7]> 
	<li class="warning">
		<span>Sie verwenden eine veraltete Version des Internet Explorers. Bitte <a href="http://www.microsoft.com/germany/windows/internet-explorer/default.aspx">aktualisieren</a> Sie Ihren Browser um das volle Repertoire dieser Seite auszuschöpfen.</span>
	</li>
<![endif]-->'
		);
		if (is_array($error)){
			for ($i = 0; $i < count($error); $i++){
				array_push($ret,
'<li class="error">
	<span>
' . str_indent($error[$i], 2) . '
	</span>
</li>');
			}
		}
		if (is_array($message)){
			for ($i = 0; $i < count($message); $i++){
				array_push($ret,
'<li class="message">
	<span>
' . str_indent($message[$i], 2) . '
	</span>
</li>');
			}
		}
		
		return join("\n", $ret);
	}
	
	
	public function write(){
		header("Content-Type: text/html; charset=" . $this->charset);
		$this->viewByName(get_class(), false, true);
	}
}


?>