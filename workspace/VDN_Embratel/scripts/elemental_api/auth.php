<?php
require_once 'configConsts.php';

// Classe que encapsula os dados de autenticaчуo da API
//
class Auth {
	protected $expires;
	protected $auth_key;
	
	public function __construct( $login,$key ) {
		$this->key = $key;
		$this->login = $login;
	}

	public function setLogin($login) {
		$this->login = $login;
	}
	
	public function setKey($key){
		$this->key = $key;
	}

	public function getLogin() {
		return $this->login;
	}	
	
	public function getExpires() {
		return $this->expires;
	}
	
	public function getAuthKey() {
		return $this->auth_key;
	}
	
	public function createAuthKey( $url ) {
		$this->expires=time() + 1200;
		
		if ( is_null($this->key) || is_null($this->login) || is_null($url) ) {
			throw new Exception('Error:createAuthKey() Login and API Key must not be null');
		}
		
		return ( $this->auth_key = md5($this->key . md5($url . $this->login . $this->key . $this->expires)) );
	}
	
}
?>