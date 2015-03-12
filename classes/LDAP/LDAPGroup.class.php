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
	/**
	 * DN where all the groups are located
	 * @var String
	 */
	public static $groupDN = "cn=groups,";
	
	/**
	 * Attribute of the group that contains the members
	 * @var String
	 */
	public static $memberAttribute = "memberuid";
	
	/**
	 * If the members are menioned by DN not by CN
	 * @var Boolean
	 */
	public static $membersByDN = false;
	
	/**
	 * Contains all the DNs of the members.
	 * @var String[]
	 */
	private $memberDNs = null;
	
	private function loadMembers(){
		if ($this->memberDNs === null){
			$this->memberDNs = $this->getAttribute(self::$memberAttribute);
		}
	}
	
	public function isMember(LDAPObject $object){
		$this->loadMembers();
		return in_array($object->dn, $this->memberDNs);
	}
	
	public function getMembers(){
		$this->loadMembers();
		$members = array();
		for ($i = 0; $i < $this->memberDNs["count"]; $i++){
			if (self::$membersByDN){
				$members[] = LDAPUser::getByDN("user", $this->memberDNs[$i]);}
			else {
				$members[] = LDAPUser::getByCN("user", $this->memberDNs[$i]);
			}
		}
		return $members;
	}
}

?>