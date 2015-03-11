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
	
	public function getMembers(){
		$members = array();
		$uids = $this->getAttribute(self::$memberAttribute);
		for ($i = 0; $i < $uids["count"]; $i++){
			if (self::$membersByDN){
				$members[] = LDAPUser::getByDN("user", $uids[$i]);}
			else {
				$members[] = LDAPUser::getByCN("user", $uids[$i]);
			}
		}
		return $members;
	}
}

?>