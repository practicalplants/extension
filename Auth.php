<?php
/**
 * Authentication plugin interface
 *
 * Copyright © 2004 Brion Vibber <brion@pobox.com>
 * http://www.mediawiki.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

/**
 * Authentication plugin interface. Instantiate a subclass of AuthPlugin
 * and set $wgAuth to it to authenticate against some external tool.
 *
 * The default behavior is not to do anything, and use the local user
 * database for all authentication. A subclass can require that all
 * accounts authenticate externally, or use it only as a fallback; also
 * you can transparently create internal wiki accounts the first time
 * someone logs in who can be authenticated externally.
 */
/*require_once(dirname(dirname(dirname(__FILE__))).'/includes/AuthPlugin.php');*/

require_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/PasswordHash/PasswordHash.php');


class PracticalPlants_Auth extends AuthPlugin {
	
	private $db;
	private $hasher;
	
	public function __construct(){
		global $wgDBtype;
		global $wgDBserver;
		global $wgDBuser;
		global $wgDBpassword;
		
		$this->hasher = new PasswordHash(8, true);
		
		$this->db = new PDO($wgDBtype.':host='.$wgDBserver.';dbname='.$ssoDbName, $wgDBuser, $wgDBpassword);
	}
	
	/**
	 * Check whether there exists a user account with the given name.
	 * The name will be normalized to MediaWiki's requirements, so
	 * you might need to munge it (for instance, for lowercase initial
	 * letters).
	 *
	 * @param $username String: username.
	 * @return bool
	 */
	public function userExists( $username ) {
		$st = $this->db->prepare('SELECT id FROM users WHERE username='.$username);
		$st->execute();
		$row = $st->fetchRow(PDO::FETCH_ASSOC);
		if($row){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Check if a username+password pair is a valid login.
	 * The name will be normalized to MediaWiki's requirements, so
	 * you might need to munge it (for instance, for lowercase initial
	 * letters).
	 *
	 * @param $username String: username.
	 * @param $password String: user password.
	 * @return bool
	 */
	public function authenticate( $username, $password ) {
		$st = $this->db->prepare('SELECT password, email FROM users WHERE username=:user');
		$st->bindParam('user',$username);
		$st->execute();
		$row = $st->fetchRow(PDO::FETCH_ASSOC);
		if($row){
			if(isset($row['password'])){
				$this->vanilla_user = $row;
				return $this->hasher->CheckPassword($password,$row['Password']);
			}
			print_r($row); exit;
		}
		return false;
	}

	/**
	 * Modify options in the login template.
	 *
	 * @param $template UserLoginTemplate object.
	 * @param $type String 'signup' or 'login'.
	 */
	public function modifyUITemplate( &$template, &$type ) {
		# Override this!
		//print_r($template); exit;
		/*$template->set( 'usedomain', false );
		
		//disable the mail new password box
	    $template->set("useemail", false);
	 
	    //disable 'remember me' box
	    $template->set("remember", false);
	 
	    $template->set("create", false);
	 
	    $template->set("domain", true);*/
	}

	/**
	 * Set the domain this plugin is supposed to use when authenticating.
	 *
	 * @param $domain String: authentication domain.
	 */
	public function setDomain( $domain ) {
		$this->domain = $domain;
	}

	/**
	 * Check to see if the specific domain is a valid domain.
	 *
	 * @param $domain String: authentication domain.
	 * @return bool
	 */
	public function validDomain( $domain ) {
		# Override this!
		return true;
	}

	/**
	 * When a user logs in, optionally fill in preferences and such.
	 * For instance, you might pull the email address or real name from the
	 * external user database.
	 *
	 * The User object is passed by reference so it can be modified; don't
	 * forget the & on your function declaration.
	 *
	 * @param $user User object
	 */
	public function updateUser( &$user ) {
		# Override this and do something
		return true;
	}

	/**
	 * Return true if the wiki should create a new local account automatically
	 * when asked to login a user who doesn't exist locally but does in the
	 * external auth database.
	 *
	 * If you don't automatically create accounts, you must still create
	 * accounts in some way. It's not possible to authenticate without
	 * a local account.
	 *
	 * This is just a question, and shouldn't perform any actions.
	 *
	 * @return Boolean
	 */
	public function autoCreate() {
		return true;
	}

	/**
	 * Allow a property change? Properties are the same as preferences
	 * and use the same keys. 'Realname' 'Emailaddress' and 'Nickname'
	 * all reference this.
	 *
	 * @param $prop string
	 *
	 * @return Boolean
	 */
	public function allowPropChange( $prop = '' ) {
		if ( $prop == 'realname' && is_callable( array( $this, 'allowRealNameChange' ) ) ) {
			return $this->allowRealNameChange();
		} elseif ( $prop == 'emailaddress' && is_callable( array( $this, 'allowEmailChange' ) ) ) {
			return $this->allowEmailChange();
		} elseif ( $prop == 'nickname' && is_callable( array( $this, 'allowNickChange' ) ) ) {
			return $this->allowNickChange();
		} else {
			return true;
		}
	}

	/**
	 * Can users change their passwords?
	 *
	 * @return bool
	 */
	public function allowPasswordChange() {
		return true;
	}

	/**
	 * Set the given password in the authentication database.
	 * As a special case, the password may be set to null to request
	 * locking the password to an unusable value, with the expectation
	 * that it will be set later through a mail reset or other method.
	 *
	 * Return true if successful.
	 *
	 * @param $user User object.
	 * @param $password String: password.
	 * @return bool
	 */
	public function setPassword( $user, $password ) {
		return true;
	}

	/**
	 * Update user information in the external authentication database.
	 * Return true if successful.
	 *
	 * @param $user User object.
	 * @return Boolean
	 */
	public function updateExternalDB( $user ) {
		return true;
	}

	/**
	 * Check to see if external accounts can be created.
	 * Return true if external accounts can be created.
	 * @return Boolean
	 */
	public function canCreateAccounts() {
		return false;
	}

	/**
	 * Add a user to the external authentication database.
	 * Return true if successful.
	 *
	 * @param $user User: only the name should be assumed valid at this point
	 * @param $password String
	 * @param $email String
	 * @param $realname String
	 * @return Boolean
	 */
	public function addUser( $user, $password, $email = '', $realname = '' ) {
		return true;
	}

	/**
	 * Return true to prevent logins that don't authenticate here from being
	 * checked against the local database's password fields.
	 *
	 * This is just a question, and shouldn't perform any actions.
	 *
	 * @return Boolean
	 */
	public function strict() {
		return true;
	}

	/**
	 * Check if a user should authenticate locally if the global authentication fails.
	 * If either this or strict() returns true, local authentication is not used.
	 *
	 * @param $username String: username.
	 * @return Boolean
	 */
	public function strictUserAuth( $username ) {
		return false;
	}

	/**
	 * When creating a user account, optionally fill in preferences and such.
	 * For instance, you might pull the email address or real name from the
	 * external user database.
	 *
	 * The User object is passed by reference so it can be modified; don't
	 * forget the & on your function declaration.
	 *
	 * @param $user User object.
	 * @param $autocreate Boolean: True if user is being autocreated on login
	 */
	public function initUser( &$user, $autocreate = false ) {
		# Override this to do something.
		if($autocreate){
			if(isset($this->vanilla_user['Email']))
				$user->setEmail($this->vanilla_user['Email']);
			
			//$user->setRealName("John Smith");
		 
		    //if using MediaWiki 1.5, we can set some e-mail options
		   
		    $user->mEmailAuthenticated = now();
		   
		    //turn on e-mail notifications by default
		    $user->setOption('enotifwatchlistpages', 1);
		    $user->setOption('enotifusertalkpages', 1);
		    $user->setOption('enotifminoredits', 1);
		    $user->setOption('enotifrevealaddr', 1);
	    }
	}

	/**
	 * If you want to munge the case of an account name before the final
	 * check, now is your chance.
	 */
	public function getCanonicalName( $username ) {
		return $username;
	}

	/**
	 * Get an instance of a User object
	 *
	 * @param $user User
	 *
	 * @return AuthPluginUser
	 */
	public function getUserInstance( User &$user ) {
		return new AuthPluginUser( $user );
	}

	/**
	 * Get a list of domains (in HTMLForm options format) used.
	 *
	 * @return array
	 */
	public function domainList() {
		return array();
	}
}

/*class PracticalPlants_AuthUser extends AuthPluginUser {
	function __construct( $user ) {
		# Override this!
	}

	public function getId() {
		# Override this!
		return -1;
	}

	public function isLocked() {
		# Override this!
		return false;
	}

	public function isHidden() {
		# Override this!
		return false;
	}

	public function resetAuthToken() {
		# Override this!
		return true;
	}
}*/
