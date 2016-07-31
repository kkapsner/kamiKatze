<?php
/**
 * BBCodeTagTrue definition file
 */

/**
 * Represention of a BBCode-tag [true].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagCompare extends BBCodeTagCondition{
	/**
	 * {@inheritdoc}
	 * @todo describe parameter
	 */
	protected $parameter = array(
		"method" => "="
	);
	
	public function exec(){
		$lastValue = null;
		foreach ($this as $child){
			$value = $child->toText();
			if ($lastValue !== null){
				switch ($this->parameter["method"]){
					case "=":
						if ($value != $lastValue){
							return false;
						}
						break;
					case "<":
						if ($value >= $lastValue){
							return false;
						}
						break;
					case ">":
						if ($value <= $lastValue){
							return false;
						}
						break;
					default:
						return false;
				}
			}
			$lastValue = $value;
		}
		return true;
	}
}