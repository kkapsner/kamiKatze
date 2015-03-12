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
	
	/**
	 * Array matching already requested IDs to DNs.
	 * @var Int[]
	 */
	protected static $idToDN = array();
	
	public static function getById($id){
		if (array_key_exists($id, self::$idToDN)){
			return self::getByDN("user", self::$idToDN[$id]);
		}
		$entry = self::$ldap->search(self::$userDN, "uidNumber=" . $id, LDAP::SCOPE_SUBTREE);
		if ($entry && ($entry = $entry->getFirstEntry())){
			$dn = $entry->dn;
			$user = self::getByDN("user", $dn);
			self::$idToDN[$id] = $dn;
			return $user;
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
		foreach (self::$ldap->search(LDAPGroup::$groupDN, LDAPGroup::$memberAttribute ."=" . $this->uid) as $group){
			$groups[] = self::getByDN("group", $group->dn);
		}
		return $groups;
	}
	
	public function getId(){
		return $this->uidNumber;
	}
}

?>
