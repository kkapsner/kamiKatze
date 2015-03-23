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
	
	public static function getById($id){
		return LDAP::$defaultLDAP->getUserById($id);
	}
	
	public static function getAll(){
		return LDAP::$defaultLDAP->defaultGroup->getMembers();
	}
	
	public function getGroups(){
		$groups = array();
		if ($this->ldap->directGroupSearch){
			foreach ($this->getAttribute($this->ldap->memberofAttribute) as $groupDN){
				$groups[] = $this->ldap->getGroup($groupDN);
			}
		}
		else {
			foreach (
				$this->ldap->search(
					$this->ldap->groupDN,
					$this->ldap->membersAttribute . "=" . $this->uid
				) as $group
			){
				$groups[] = $this->ldap->getGroupByDN($group->dn);
			}
		}
		return $groups;
	}
	
	public function getId(){
		return $this->uidNumber;
	}
}

?>