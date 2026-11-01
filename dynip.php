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
require_once __DIR__ . '/database.php';

$database = new MySqlDatabase();

function build_dns_config() {
  global $database;
  $file = fopen(DNS_FILE, 'w');
  $hosts = $database->getAddresses();
  foreach ($hosts as $name => $addr) {
    if ($ip = $addr['ipv4']) {
      fprintf($file, "%s\tIN A\t%s\n", $name, $ip);
    }
    if ($ip = $addr['ipv6']) {
      fprintf($file, "%s\tIN AAAA\t%s\n", $name, $ip);
    }
  }
  fclose($file);
}

function incr_serial(): void
{
  $text = file_get_contents(DNS_DB);
  $lines = explode(PHP_EOL, $text);
  $file = fopen(DNS_DB, 'w');
  $serial = NULL;
  $matches = array();
  foreach ($lines as $line) {
    if (is_null($serial) && (1 == preg_match('/(\s*)(\d+)(\s*);\s*serial/', $line, $matches))) {
      $serial = 1 + intval($matches[2]);
      echo "new serial is $serial" . PHP_EOL;
      $line = $matches[1] . $serial . $matches[3] . '; serial';
    }
    fwrite($file, $line . PHP_EOL);
  }
  fclose($file);
}

/**
 *
 */
function rebuild(): void
{
  printf("<p>bind database rebuild (" . posix_getuid() . "/" . posix_geteuid() . ")</p>\n");
  $exit = 0;
  echo "<pre>\n";
  build_dns_config();
  incr_serial();
  $result = system(DNS_REBUILD, $exit);
  echo "</pre>\n";
  if ($result === false or $exit != 0) {
    printf("<p>bind database rebuild failed: " . $result . "($exit)</p>\n");
  }
  else {
    printf("<p>bind database rebuilt.</p>\n");
  }
}

/**
 * Authenticate a host with a password.
 *
 * @param string $host
 *   The host we want to authenticate.
 * @param string $pass
 *   The plaintext password.
 * 
 * @return boolean
 *   true if password matches.
 */
function check_passwd(string $host, string $pass): bool
{
  global $database;
  
  $dbpass = $database->getPassword($host);
  if ($dbpass) {
    if (password_verify($pass, $dbpass)) {
      return true;
    }

    // Legacy passwords are hashed with sha256 before they get stored:
    $compat = password_hash(hash('sha256', $pass), PASSWORD_DEFAULT);
    return $compat && password_verify($compat, $dbpass);
  }
  return false;
}

function update_host(string $host, string $ip): bool
{
  global $database;
  return $database->setAddress($host, $ip);
}

function delete_host(string $host) {
  global $database;
  return $database->setAddress($host, NULL);
}

function error(string $message, int $status = 400) {
  http_response_code($status);
  return $message;
}

function main() {
  $norebuild = (bool) $_POST['norebuild'];

  $post_ip = $_POST['ip'];
  if (empty($post_ip)) {
    $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
  }
  else {
    $ip = filter_var($post_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
  }
  if ($ip) {
    $action = $_POST['action'];
    $host = $_POST['host'];
    $pass = $_POST['pass'];
    if (!$pass) {
      return error("request requires a password");
    }
    if (!$host) {
      return error("request requires a host");
    }
    switch ($action) {
      case "register":
        if (check_passwd($host, $pass) === true) {
          update_host($host, $ip);
          printf("<p>register successful</p>\n");
          if ($norebuild == false) {
            rebuild();
          }
        }
        else {
          return error("<p>register: invalid host or password</p>\n");
        }
        break;

      case "unregister":
        if (check_passwd($host, $pass) === true) {
          delete_host($host);
          if ($norebuild == false) {
            rebuild();
          }
        }
        else {
          return error("<p>unregister: invalid host or password</p>\n");
        }
        break;

      default:
        return error("<p>ERROR: unknown action $action</p>");
    }
  }
  else {
    return "<p>invalid IP: '" . $ip . "'</p>\n";
  }
}

$response = main();
?>
<html>
<head>
<title>KN-Bremen DynIP</title>
</head>
<body bgcolor="white">
<?php
echo $response;
?>
</body>
</html>
