<?php

/**
 * Description of EmailAddressGroup
 *
 * @author kkapsner
 */
class EmailAddressGroup implements EmailAddressInterface, SeekableIterator{
	/**
	 * The groups name
	 * @var string
	 */
	public $name;
	
	/**
	 * The charset of the groups name. Default: EmailEncoder::$defaultCharset
	 * @var string
	 */
	public $charset = NULL;
	
	/**
	 *
	 * @var array 
	 */
	protected $members = array();

	public function __construct($name){
		$this->name = $name;
	}

	/**
	 * Checks if all member-addresses are valid.
	 * @return bool
	 */
	public function isValid(){
		foreach ($this->members as $member){
			if (!$member->isValid()){
				return false;
			}
		}
		return true;
	}

	/**
	 * Adds an address to the group.
	 * @param EmailAddress $address
	 */
	public function addAddress(EmailAddress $address){
		$members[] = $address;
	}

	/**
	 * Removes an addres from the group.
	 * @param EmailAddress $address
	 */
	public function removeAddress(EmailAddress $address){
		$pos = array_search($address, $this->members, true);
		if ($pos !== false){
			array_splice($this->members, $pos, 1);
		}
	}
	
	
	public function toHeaderEncoded($alreadyConsumedChars = 0){
		$ret = "";
		#if ($this->name){
			$ret = EmailEncoder::escapePhrase($this->name, $alreadyConsumedChars, $this->charset);
			$ret .= ":";
		#}
		$cMember = count($this->members);
		for ($i = 0; $i < $cMember; $i++){
			$ret .= ($i === 0? " ": ",");
			$alreadyConsumedChars = EmailEncoder::getLastLineLength($ret);
			$ret .= $this->members[$i]->toHeaderEncoded($alreadyConsumedChars);
		}
		#if ($this->name){
			$ret .= ";";
		#}
		return $ret;
	}

	public function __toString(){
		return $this->name . ": " . implode(", ", $this->members) . ";";
	}

	#seekable iterator
	/**
	 * current pointer for the iterator
	 * @var int
	 */
	protected $current = 0;
	public function current(){
		return $this->members[$this->current];
	}

	public function key(){
		return $this->current;
	}

	public function next(){
		$this->current++;
	}

	public function rewind(){
		$this->current = 0;
	}

	public function seek($position){
		$this->current = $position;
	}

	public function valid(){
		return $this->current >= 0 && $this->current < count($this->members);
	}

}

?>
