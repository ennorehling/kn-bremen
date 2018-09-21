<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/settings.php';

interface DatabaseInterface {
  /**
   * Get the password hash for a single host.
   *
   * @param string $host
   *
   * @return string|null|false
   */
  public function getPassword($host);

  /**
   * Get IP addresses for all known hosts.
   *
   * @return array|false
   */
  public function getAddresses();

  /**
   * 
   * @param string $host
   * @param string $addr
   */
  public function setAddress($host, $addr);

  /**
   * 
   * @param string $host
   * @param string $pass
   */
  public function setPassword($host, $pass);

}

class MySqlDatabase implements DatabaseInterface {
  protected $conn;

  function __construct() {
      $this->conn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DBNAME);
  }
    
  function __destruct() {
    $this->conn->close();
  }

  public function getPassword($host) {
    $result = FALSE;
    $stmt = $this->conn->prepare('SELECT pass FROM passwd WHERE host=?');
    $stmt->bind_param('s', $host);
    if ($stmt->execute()) {
      $pass = '';
      $stmt->bind_result($pass);
      $fetch = $stmt->fetch();
      if (is_null($fetch)) {
        $result = NULL;
      }
      elseif ($fetch === TRUE) {
        $result = $pass;
      }
    }
    $stmt->close();
    return $result;
  }

  public function setPassword($host, $pass) {
    $stmt = $this->conn->prepare('INSERT INTO passwd (host, pass) VALUES (?,?) ' .
            'ON DUPLICATE KEY UPDATE pass=?');
    $stmt->bind_param('sss', $host, $pass, $pass);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
  }

  public function getAddresses() {
    $result = array();
    $stmt = $this->conn->prepare("SELECT host, ip FROM mappings WHERE ip REGEXP '\d+\.\d+\.\d+\.\d+'");
    if ($stmt->execute()) {
      $host = '';
      $ip = '';
      $stmt->bind_result($host, $ip);
      while ($stmt->fetch()) {
        $result[$host] = $ip;
      }
    }
    $stmt->close();
    return $result;
  }

  public function setAddress($host, $addr) {
    if (is_null($addr)) {
      $stmt = $this->conn->prepare('DELETE FROM mappings WHERE host=?');
      $stmt->bind_param('s', $host);
    }
    else {
      $stmt = $this->conn->prepare('INSERT INTO mappings (host, ip, created) VALUES (?,?,NOW()) ' .
              'ON DUPLICATE KEY UPDATE ip=?');
      $stmt->bind_param('sss', $host, $addr, $addr);
    }
    $result = $stmt->execute();
    $stmt->close();
    return $result;
  }
}
