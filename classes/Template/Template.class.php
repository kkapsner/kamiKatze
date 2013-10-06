<?php
/**
 * Template definition file
 */

/**
 * Template class for HTML templates
 *
 * @author Korbinian Kapsner
 * @package Template
 */
abstract class Template extends ViewableHTML{
	/**
	 * The titel of the page
	 * @var string
	 */
	public $title;
	
	/**
	 * All the styles that shall be included in the template.
	 * @var ViewableHTMLTagStyle[]
	 */
	protected $style = array();
	
	/**
	 * The standard path to style files.
	 * @var string
	 */
	public $stylePlace;
	
	/**
	 * All the scripts that shall be included in the template header.
	 * @var ViewableHTMLTagScript[]
	 */
	protected $script = array();
	
	/**
	 * All the scripts that shall be included in the template just before the closing </body>.
	 * @var ViewableHTMLTagScript[]
	 */
	protected $lateScript = array();

	/**
	 * The standard path to script files.
	 * @var string
	 */
	public $scriptPlace;

	/**
	 * The additional head tags.
	 * @var ViewableHTMLTag
	 */
	protected $headTags = array();

	/**
	 * The meta keys and values.
	 * @var array
	 */
	public $meta = array();

	/**
	 * Path to the favicon. Template::$stylePlace is prepended to it.
	 * @var string
	 */
	public $favicon = "";

	/**
	 * Adds a style to the template.
	 *
	 * @param string|ViewableHTMLTagStyle $name the style filename or directly a style tag to be added.
	 * @param boolean $addSTYLE if the standard style path should be added to the filename.
	 * @return ViewableHTMLTagStyle the added style
	 */
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

	/**
	 * Adds a script to the template.
	 *
	 * @param string|ViewableHTMLTagScript $name the script filename or directly a script tag to be added.
	 * @param boolean $addSCRIPT if the standard script path should be added to the filename. Only available if $name is a string.
	 * @param boolean $late if the script should be added late - i.e. direct before the closing </body>.
	 * @return ViewableHTMLTagStyle the added script
	 */
	public function addScript($name, $addSCRIPT = true, $late = true){
		if (!is_a($name, "ViewableHTMLTagScript")){
			$script = new ViewableHTMLTagScript();
			$script->setHTMLAttribute("src", ($addSCRIPT? $this->scriptPlace: "") . $name, true);
		}
		else {
			$script = $name;
			$late = $addSCRIPT;
		}
		if ($late){
			array_push($this->lateScript, $script);
		}
		else {
			array_push($this->script, $script);
		}
		return $script;
	}
	
	/**
	 * Adds a head tag to the template.
	 * 
	 * @param ViewableHTMLTag $tag
	 */
	public function addHeadTag(ViewableHTMLTag $tag){
		$this->headTags[] = $tag;
	}

	/**
	 * Gets an HTML <li> representation of errors and messages.
	 *
	 * @global type $error
	 * @global type $message
	 * @return type
	 * @todo check if this is realy neccessary
	 */
	protected function getErrorLi(){
		global $error;
		global $message;
		
		$ret = array('<li style="display: none;"></li>'/*,
'<!--[if lte IE 7]> 
	<li class="warning">
		<span>Sie verwenden eine veraltete Version des Internet Explorers. Bitte <a href="http://www.microsoft.com/germany/windows/internet-explorer/default.aspx">aktualisieren</a> Sie Ihren Browser um das volle Repertoire dieser Seite auszuschöpfen.</span>
	</li>
<![endif]-->'*/
		);
		if (is_array($error)){
			for ($i = 0; $i < count($error); $i++){
				array_push($ret,
'<li class="error">
	<span>
' . $error[$i] . '
	</span>
</li>');
			}
		}
		if (is_array($message)){
			for ($i = 0; $i < count($message); $i++){
				array_push($ret,
'<li class="message">
	<span>
' . $message[$i] . '
	</span>
</li>');
			}
		}
		
		return join("\n", $ret);
	}
	
	/**
	 * Writes the template to the output.
	 */
	public function write(){
		header("Content-Type: text/html; charset=" . $this->charset);
		$this->viewByName(get_class(), false, true);
	}
}


?>