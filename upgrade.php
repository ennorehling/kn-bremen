<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/database.php';

$db = new MySqlDatabase();

$hosts = $db->getAddresses();
foreach ($hosts as $host => $ip) {
  $pass = $db->getPassword($host);
  if ($pass[0] != '$') {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $db->setPassword($host, $pass);
  }
}
