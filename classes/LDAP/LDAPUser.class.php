<?php

/**
 * LDAPUser definition file
 */

/**
 * Description of LDAPUser
 *
 * @author kkapsner
 */
class LDAPUser extends LDAPObject implements DBItemExternalClassInterface{
	
	/**
	 * DN where all the users are located
	 * @var String
	 */
	public static $userDN = "cn=users,";
	
	/**
	 * Array of all available users.
	 * @var LDAPUser[]
	 */
	public static $all = array();
	
	public static function getById($id){
		$entry = self::$ldap->search(self::$userDN, "uidNumber=" . $id, LDAP::SCOPE_SUBTREE);
		if ($entry && ($entry = $entry->getFirstEntry())){
			$dn = $entry->dn;
			return self::getByDN("user", $dn);
		}
		else {
			return null;
		}
	}
	
	public static function getAll(){
		return self::$all;
	}
	
	public function getGroups(){
		$groups = array();
		foreach (self::$ldap->search(LDAPGroup::$groupDN, "memberuid=" . $this->uid) as $group){
			$groups[] = self::getByDN("group", $group->dn);
		}
		return $groups;
	}
	
	public function getId(){
		return $this->uidNumber;
	}
}

?>
