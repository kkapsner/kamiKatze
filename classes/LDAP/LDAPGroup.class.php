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
	
	public function getMembers(){
		$members = array();
		$uids = $this->getAttribute("memberuid");
		for ($i = 0; $i < $uids["count"]; $i++){
			$members[] = LDAPUser::getByCN("user", $uids[$i]);
		}
		return $members;
	}
}

?>