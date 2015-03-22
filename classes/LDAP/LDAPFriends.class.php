<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author kkapsner
 */
abstract class LDAPFriends extends ViewableHTML{
	protected static function createLDAPObject(LDAP $ldap, $type, $dn){
		throw new BadFunctionCallException("Not implemented");
	}
	protected function getLDAPObject($type, $dn){
		throw new BadMethodCallException("Only callable from LDAP.");
	}
}
