<?php
class SessionTokenManager {
	private $_db;
	
	public function __construct($db) {
		$this->setDb($db);
	}
	
	public function get($id) {
		$id = (int) $id;
		$result = pg_prepare($this->_db, '', 'SELECT * FROM session_token WHERE client_id = $1');
		$result = pg_execute($this->_db, '', array($id)) or die('Query failed: ' . pg_last_error($this->_db));
		if (pg_num_rows($result) != 1) {
			return new SessionToken(array('client_id' => $id, 'token' => ''));
		}
		
		$data = pg_fetch_array($result, 0, PGSQL_ASSOC);
		pg_free_result($result);
		
		return new SessionToken($data);
		
	}
	
	public function upsert(SessionToken $token) {
		$result = pg_prepare($this->_db, "", 'UPDATE session_token SET token = $1 WHERE client_id = $2') or die('Query failed: ' . pg_last_error($this->_db));;
		$result = pg_execute($this->_db, "", array($token->token(), $token->client_id())) or die('Query failed: ' . pg_last_error($this->_db));
		pg_free_result($result);
		
		$result = pg_prepare($this->_db, "", 'INSERT INTO session_token (client_id, token) SELECT $1, $2 WHERE NOT EXISTS (SELECT 1 FROM session_token WHERE client_id = $1)') or die('Query failed: ' . pg_last_error($this->_db));;
		$result = pg_execute($this->_db, "", array($token->client_id(), $token->token())) or die('Query failed: ' . pg_last_error($this->_db));
		pg_free_result($result);
	}
	
	public function remove($id) {
		$id = (int) $id;
		$result = pg_prepare($this->_db, '', 'DELETE FROM session_token WHERE client_id = $1');
		$result = pg_execute($this->_db, '', array($id)) or die('Query failed: ' . pg_last_error($this->_db));
		pg_free_result($result);
	}
	
	public function validateCookie() {
		$cookie = isset($_COOKIE['session']) ? $_COOKIE['session'] : '';
		if ($cookie) {
			//first authenthicate the cookie
			list ($id, $token, $mac) = explode(':', $cookie);
			if (!Crypto::compareHash(Crypto::mac($id . ':' . $token), $mac)) {
				throw new Exception("Cookie corrupted");
			}
			
			//second match the cookie against the DB
			$sessionToken = $this->get($id);
			if (!Crypto::compareHash($sessionToken->token(), $token)) {
				throw new Exception("Session tokens do not match");
			}
			
			return $id;
		}
		return -1;
	}
	
	public function setDb($db) {
		$this->_db = $db;
	}
	
}