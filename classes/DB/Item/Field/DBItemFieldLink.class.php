<?php
/**
 * DBItemFieldLink definition file
 */

/**
 * Description of DBItemFieldLink
 *
 * @author Korbinian Kapsner
 */
class DBItemFieldLink extends DBItemFieldNative{
	/**
	 * Prefix for the link URL. In most cases this is something like "http://"
	 * @var string
	 */
	protected $linkPrefix = "";

	/**
	 * Postfic for the link URL.
	 * @var string
	 */
	protected $linkPostfix = "";

	/**
	 * Flag if the link is external and should be opened in a new window/tab.
	 * @var boolean
	 */
	protected $externalLink = true;

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $options
	 */
	protected function parseOptions(DBItemClassSpecifier $classSpecifier, $options){
		parent::parseOptions($classSpecifier, $options);

		$this->linkPrefix = array_read_key("linkPrefix", $options, $this->linkPrefix);
		$this->linkPostfix = array_read_key("linkPostfix", $options, $this->linkPostfix);
		$this->externalLink = array_read_key("externalLink", $options, $this->externalLink);
	}

}

?>
