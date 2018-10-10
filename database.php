<?php

/* 
 * Copyright (C) 2018 Enno Rehling
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
    $stmt = $this->conn->prepare('SELECT host, ip FROM mappings WHERE ip REGEXP ?');
    $regexp = '\d+\.\d+\.\d+\.\d+';
    $stmt->bind_param('s', $regexp);
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
