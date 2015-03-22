<?php
/**
 * LDAP definition file
 */

/**
 * OO wrapper for the PHP ldap_ functions. Provides also some additional functionality.
 *
 * @author Korbinian Kapsner
 * @package LDAP
 * @todo extend functionality
 */
class LDAP extends LDAPResourceContainer{
	/**
	 * Search scope base. Only one entry returned.
	 */
	const SCOPE_BASE = 0;
	/**
	 * Search scope one level. The search is only performed on the level of the current dn.
	 */
	const SCOPE_ONE_LEVEL = 1;
	/**
	 * Search scope subtree. The search is performed on the complete subtree under the current dn.
	 */
	const SCOPE_SUBTREE = 2;

	const DEREF_NEVER = LDAP_DEREF_NEVER;
	const DEREF_SEARCHING = LDAP_DEREF_SEARCHING;
	const DEREF_FINDING = LDAP_DEREF_FINDING;
	const DEREF_ALWAYS = LDAP_DEREF_ALWAYS;
	
	/**
	 * The default LDAP to use. This static field is populated by the first
	 * created LDAP, but can be changed any time.
	 * @var LDAP
	 */
	public static $defaultLDAP = null;
	
	/**
	 * Time limit (in seconds) for each search.
	 * Zero means no (client) time limit. Server limits can not be changed.
	 * @var int
	 */
	public $timeLimit = 0;

	/**
	 * Size limit of returned search items.
	 * Zero means no (client) limit. Server limits can not be changed.
	 * @var int
	 */
	public $sizeLimit = 0;

	/**
	 * How dereferencing should be handled.
	 * @var int
	 */
	public $dereferencing = LDAP::DEREF_NEVER;
	
	/**
	 * The base DN of the LDAP
	 * @var null|String
	 */
	public $baseDN = null;
	
	/**
	 * The DN of the container that contains the users
	 * @var Sring
	 */
	public $userDN = "cn=users,";
	
	/**
	 * The DN of the container that contains the groups
	 * @var String
	 */
	public $groupDN = "cn=groups,";
	
	/**
	 * The default LDAP group to use. This static field is populated by the
	 * first created LDAP group, but can be changed any time.
	 * @var LDAPGroup
	 */
	public $defaultGroup = null;
	
	/**
	 * Attribute name of a user that contains all the groups she/he belongs to
	 * @var String
	 */
	public $memberofAttribute = "memberof";
	
	/**
	 * Attribute name of a group that contains all the members
	 * @var String
	 */
	public $membersAttribute = "member";
	
	/**
	 * If the groups of a user should be obtained directly of via reverse
	 * lookup.
	 * @var boolean
	 */
	public $directGroupSearch = true;
	

	/**
	 * Array containing the parts of the current working directory
	 * @var string[]
	 */
	protected $cwd = array();

	/**
	 * The hostname of the server.
	 * @var string
	 */
	protected $hostname;

	/**
	 * The LDAP-port on the server.
	 * @var int
	 */
	protected $port;

	/**
	 * Internal flag if the connection is bound.
	 * @var boolean
	 */
	protected $isBound = false;

	/**
	 * Constructor of LDAP. Calls {@see LDAP::connect()}.
	 *
	 * @param string $hostname
	 * @param int $port
	 */
	public function __construct($hostname = null, $port = 389){
		if (!self::$defaultLDAP){
			self::$defaultLDAP = $this;
		}
		$this->connect($hostname, $port);
	}

	/**
	 * Connects to a LDAP server
	 *
	 * @param string $hostname the server path
	 * @param int $port the LDAP port on the server
	 * @return boolean if the connect was successful
	 */
	public function connect($hostname = null, $port = 389){
		if ($this->isConnected()){
			return false;
		}
		if ($hostname !== null){
			$this->hostname = $hostname;
		}
		else {
			$hostname = $this->hostname;
		}
		$this->port = $port;
		$this->resource = ldap_connect($hostname, $port);
		if ($this->isConnected()){
			$this->emit(new Event("connect", $this));
			$this->protocolVersion = 3;
			return true;
		}
		else {
			$this->emit(new EventError("connectError", $this, new LDAPConnectionException()));
			return false;
		}
	}

	/**
	 * Checks if there is a connection to a server.
	 * @return boolean
	 * @todo lazy connecting makes this difficult...
	 */
	public function isConnected(){
		return $this->resource !== false;
	}

	/**
	 * Binds the connection anonymous or with a specific dn and password.
	 *
	 * @param string $bindDN
	 * @param string $bindPwd
	 * @return boolean If the binding was successful
	 */
	public function bind($bindDN = null, $bindPwd = null){
		if (@ldap_bind($this->resource, $bindDN, $bindPwd)){
			$this->isBound = true;
			$this->emit(new Event("bind", $this));
			return true;
		}
		else {
			$this->emit(new EventError("bindError", $this, $this->getException()));
			return false;
		}
	}

	/**
	 * Unbind the connection.
	 *
	 * @return boolean If the unbinding was successful.
	 */
	public function unbind(){
		if ($this->isBound()){
			if (ldap_unbind($this->resource)){
				$this->emit(new Event("unbind", $this));
				$this->isBound = false;
				return true;
			}
			else {
				$this->emit(new EventError("unbindError", $this, $this->getException()));
				return false;
			}
		}
		else {
			return false;
		}
	}


	/**
	 * Checks if the connection is bound to a user.
	 *
	 * @return boolean If the connection is bound
	 */
	public function isBound(){
		return $this->isBound;
	}

	/**
	 * Getter for the last occured error.
	 * 
	 * @return LDAPException
	 */
	public function getException(){
		return new LDAPException(ldap_error($this->resource), ldap_errno($this->resource));
	}

	/**
	 * Gets the rootDSE from the server.
	 *
	 * @return LDAPResultEntry|false the rootDSE on success or false on failure
	 */
	public function getRootDSE(){
		$search = $this->search("", "objectClass=*", LDAP::SCOPE_BASE);
		if ($search){
			return $search->getFirstEntry();
		}
		else {
			return false;
		}
	}
	
	/**
	 * Use this to obtian the hostname. If the hostname is an IP-address the
	 * dnshostname entry of the root DSE ist returned.
	 * 
	 * @return String the hostname of the LDAP
	 */
	public function getHostname(){
		if (!preg_match("/(?:^|\\.).*[a-z].*(?:\\.|$)/i", $this->hostname)){
			$this->getRootDSE()->dnshostname[0];
		}
		else {
			return $this->hostname;
		}
	}
	
	/**
	 * Use this to obtain the base DN. If the base DN is not set it will be
	 * derived from the hostname.
	 * 
	 * @return String the base DN
	 */
	public function getBaseDN(){
		if (!$this->baseDN){
			$this->baseDN = "dc=" . str_replace(".", ",dc=", $this->getHostname());
		}
		return $this->baseDN;
	}

	/**
	 * Searches in the LDAP.
	 *
	 * @param string $baseDN
	 * @param string $filter
	 * @param int $scope
	 * @param array $attributes
	 * @param boolean $attributesOnly
	 * @return LDAPResult|boolean
	 * @throws InvalidArgumentException
	 */
	public function search($baseDN, $filter, $scope = LDAP::SCOPE_SUBTREE,
		$attributes = array(), $attributesOnly = false
	){
		$baseDN = $this->resolvePath($baseDN);
		switch ($scope){
			case LDAP::SCOPE_BASE:
				$resultResource = @ldap_read($this->resource, $baseDN, $filter, $attributes,
					$attributesOnly, $this->sizeLimit, $this->timeLimit, $this->dereferencing);
				break;
			case LDAP::SCOPE_ONE_LEVEL:
				$resultResource = @ldap_list($this->resource, $baseDN, $filter, $attributes,
					$attributesOnly, $this->sizeLimit, $this->timeLimit, $this->dereferencing);
				break;
			case LDAP::SCOPE_SUBTREE:
				$resultResource = @ldap_search($this->resource, $baseDN, $filter, $attributes,
					$attributesOnly, $this->sizeLimit, $this->timeLimit, $this->dereferencing);
				break;
			default:
				throw new InvalidArgumentException("Argument scope must be one of " .
					"LDAP::SCOPE_BASE, LDAP::SCOPE_ONE_LEVEL or LDAP::SCOPE_SUBTREE");
		}
		
		if ($resultResource !== false){
			$this->emit(new Event("search", $this));
			$result = new LDAPResult($this, $resultResource);
			return $result;
		}
		else {
			$this->emit(new EventError("searchError", $this, $this->getException()));
			return false;
		}
	}

	/**
	 * Magic method __get. Used to get connection options.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws InvalidArgumentException
	 * @throws BadMethodCallException
	 */
	public function __get($name){
		if ($this->isConnected()){
			$realName = "LDAP_OPT_" . strtoupper(preg_replace("/([a-z])([A-Z])/", "$1_$2", $name));
			if (defined($realName)){
				if (ldap_get_option($this->resource, constant($realName), $value)){
					return $value;
				}
				else {
					$this->emit(new EventError("optionGetError", $this, $this->getException()));
				}
			}
			else {
				throw new InvalidArgumentException("No connection option " . $name . ".");
			}
		}
		else {
			throw new BadMethodCallException("Connection options can only be read after connecting.");
		}
	}
	
	/**
	 * Magic method __set. Used to set connection options.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @throws InvalidArgumentException
	 * @throws BadMethodCallException
	 */
	public function __set($name, $value){
		if ($this->isConnected()){
			$realName = "LDAP_OPT_" . strtoupper(preg_replace("/([a-z])([A-Z])/", "$1_$2", $name));
			if (defined($realName)){
				if (!ldap_set_option($this->resource, constant($realName), $value)){
					$this->emit(new EventError("optionSetError", $this, $this->getException()));
				}
			}
			else {
				throw new InvalidArgumentException("No connection option " . $name . ".");
			}
		}
		else {
			throw new BadMethodCallException("Connection options can only be set after connecting.");
		}
	}

	/**
	 * Magic function __call. Wraps all not explicit implemented ldap_ functions.
	 *
	 * @param string $name
	 * @param mixed[] $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments){
		if (function_exists("ldap_" . $name)){
			$func = new ReflectionFunction("ldap_" . $name);
			/* @var $parameter ReflectionParameter[] */
			$parameter = $func->getParameters();
			if (preg_match("/^link(|_identifier)$/i", $parameter[0]->name)){
				array_unshift($arguments, $this->resource);
			}
			return $func->invokeArgs($arguments);
		}
		else {
			throw new BadMethodCallException("No method  called " . $name . " found.");
		}
	}

	/**
	 * Changes the LDAP directory.
	 *
	 * @see LDAP::resolvePath()
	 * @param string $path the new path
	 */
	public function cd($path){
		$this->cwd = $this->resolvePath($path, true);
	}

	/**
	 * Returns the current LDAP directory.
	 * 
	 * @return string
	 */
	public function cwd(){
		return implode(",", $this->cwd);
	}

	/**
	 * Resolves a LDAP path.
	 *
	 * @param string $path
	 * @param boolean $arrayReturn
	 * @return string|string[]
	 * @todo document exact behaviour
	 */
	public function resolvePath($path, $arrayReturn = false){
		$pathParts = preg_split("/,+/", $path);
		$len = count($pathParts);
		switch ($pathParts[$len - 1]){
			case "":
				if ($len > 1){
					$rootParts = explode(",", $this->getBaseDN());
					$pathParts = array_merge($pathParts, $rootParts);
				}
				break;
			case ".":
			case "..":
				$pathParts = array_merge($pathParts, $this->cwd);
				break;
		}

		for($i = 0; $i < count($pathParts); $i++){
			switch ($pathParts[$i]){
				case "":
				case ".":
					array_splice($pathParts, $i, 1);
					$i--;
					break;
				case "..":
					array_splice($pathParts, $i, 2);
					$i--;
					break;
			}
		}

		if ($arrayReturn){
			return $pathParts;
		}
		else {
			return implode(",", $pathParts);
		}
	}
	
	// LDAP object functions
	
	/**
	 * keeps track of all LDAP objects
	 * @var LDAPObject[]
	 */
	protected $objectInstances = array();
	
	/**
	 * keeps track of all id -> DN relations
	 * @var String[]
	 */
	protected $idToDN = array();
	
	public function getUser($identifier){
		return $this->getObject("user", $identifier);
	}
	public function getUserById($identifier){
		return $this->getObjectById("user", $identifier);
	}
	public function getUserByCN($identifier){
		return $this->getObjectByCN("user", $identifier);
	}
	public function getUserByDN($identifier){
		return $this->getObjectByDN("user", $identifier);
	}
	
	public function getGroup($identifier){
		return $this->getObject("group", $identifier);
	}
	public function getGroupById($identifier){
		return $this->getObjectById("group", $identifier);
	}
	public function getGroupByCN($identifier){
		return $this->getObjectByCN("group", $identifier);
	}
	public function getGroupByDN($identifier){
		return $this->getObjectByDN("group", $identifier);
	}
	
	protected function getObjectById($type, $identifier){
		if (array_key_exists($identifier, $this->idToDN)){
			return $this->getLDAPObject($type, $this->idToDN[$identifier]);
		}
		switch (strToLower($type)){
			case "user":
				$searchBase = $this->userDN;
				break;
			case "group":
				$searchBase = $this->groupDN;
				break;
			default:
				$searchBase = $this->baseDN;
		}
		$entry = $this->search($searchBase, "uidNumber=" . $identifier, LDAP::SCOPE_SUBTREE);
		if ($entry && ($entry = $entry->getFirstEntry())){
			$dn = $entry->dn;
			$this->idToDN[$identifier] = $dn;
			return $this->getObjectByDN($type, $dn);
		}
		else {
			return null;
		}
	}
	
	protected function getObjectByCN($type, $cn){
		$dn = $this->search(",", "(|(cn=$cn)(uid=$cn))", LDAP::SCOPE_SUBTREE);
		if ($dn && ($dn = $dn->getFirstEntry()) && ($dn = $dn->dn)){
			return $this->getObjectByDN($type, $dn);
		}
		else {
			return null;
		}
	}
	
	protected function getObjectByDN($type, $dn){
		return $this->getLDAPObject($type, $dn);
	}
	
	protected function getObject($type, $identifier){
		if (is_numeric($identifier)){
			return $this->getObjectById($type, $identifier);
		}
		elseif (preg_match("/^(?:cn|dn|ou)=/i", $identifier)){
			return $this->getObjectByDN($type, $identifier);
		}
		else {
			return $this->getObjectByCN($type, $identifier);
		}
	}
	
	// LDAP friends
	protected static function createLDAPObject(LDAP $ldap, $type, $dn){
		LDAPObject::createLDAPObject($ldap, $type, $dn);
	}
	
	/**
	 * 
	 * @param type $type
	 * @param type $dn
	 * @return LDAPObject
	 */
	protected function getLDAPObject($type, $dn){
		if (!key_exists($dn, $this->objectInstances)){
			$this->objectInstances[$dn] = LDAPObject::createLDAPObject($this, $type, $dn);
		}
		return $this->objectInstances[$dn];
	}
}

?>
