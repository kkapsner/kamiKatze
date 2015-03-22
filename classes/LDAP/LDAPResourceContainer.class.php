<?php
/**
 * LDAPResourceContainer definition file
 */

/**
 * Abstract parent class for all types of LDAP resource representer.
 *
 * This is neccessary to access the resource of the connection from the other classes.
 * @author Korbinian Kapsner
 * @package LDAP
 */
abstract class LDAPResourceContainer extends LDAPFriends{
	/**
	 * The native LDAP resource.
	 * 
	 * @var resource|false
	 */
	protected $resource = false;
}

?>
