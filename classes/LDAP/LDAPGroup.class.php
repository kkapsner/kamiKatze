<?php

/**
 * LDAPGroup definition file
 */

/**
 * Description of LDAPGroup
 *
 * @author kkapsner
 */
class LDAPGroup extends LDAPObject{
	
	protected function __construct(LDAP $ldap, $dn){
		parent::__construct($ldap, $dn);
		if (!$ldap->defaultGroup){
			$ldap->defaultGroup = $this;
		}
	}
	/**
	 * Contains all the DNs of the members.
	 * @var String[]
	 */
	private $memberDNs = null;
	
	private function loadMembers(){
		if ($this->memberDNs === null){
			$this->memberDNs = $this->getAttribute($this->ldap->membersAttribute);
			if (!$this->ldap->caseSensitive){
				$this->memberDNs = array_map("strtolower", $this->memberDNs);
			}
		}
	}
	
	public function isMember(LDAPUser $object){
		$this->loadMembers();
		return in_array($object->dn, $this->memberDNs);
	}
	
	public function getMembers(){
		$this->loadMembers();
		$members = array();
		for ($i = 0; $i < $this->memberDNs["count"]; $i++){
			$members[] = $this->ldap->getUser($this->memberDNs[$i]);
		}
		return $members;
	}
}

?>