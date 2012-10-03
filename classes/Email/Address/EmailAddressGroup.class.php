<?php
/**
 * EmailAddressGroup definition file
 */

/**
 * Representation of a group of email addresses
 *
 * @author Korbinian Kapser
 * @package Email\Address
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
	 * Array of the groups members
	 *
	 * @var EmailAddressInterface[]
	 */
	protected $members = array();

	/**
	 * Constructor of EmaiAddressGroup
	 *
	 * @param type $name the name of the group
	 */
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
	
	/**
	 * {@inheritdoc}
	 *
	 * @param int $alreadyConsumedChars
	 * @return string
	 */
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

	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function __toString(){
		return $this->name . ": " . implode(", ", $this->members) . ";";
	}

	#seekable iterator
	/**
	 * current pointer for the iterator
	 * @var int
	 */
	protected $current = 0;

	/**
	 * {@inheritdoc}
	 *
	 * @return EmailAddressInterface
	 */
	public function current(){
		return $this->members[$this->current];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return int
	 */
	public function key(){
		return $this->current;
	}

	/**
	 * {@inheritdoc}
	 */
	public function next(){
		$this->current++;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind(){
		$this->current = 0;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $position
	 */
	public function seek($position){
		$this->current = $position;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	public function valid(){
		return $this->current >= 0 && $this->current < count($this->members);
	}

}

?>
