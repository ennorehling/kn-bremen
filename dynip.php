<?php

/**
 * @file
 */
?>
<html>
<head>
<title>KN-Bremen DynIP</title>
</head>
<body bgcolor="white">
<?php

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/database.php';

$database = new MySqlDatabase();

function build_dns_config() {
  global $database;
  $file = fopen(DNS_FILE, 'w');
  $hosts = $database->getAddresses();
  foreach ($hosts as $host => $ip) {
    fprintf($file, "%s\tIN A\t%s", $host, $ip);
  }
  fclose($file);
}

/**
 *
 */
function rebuild() {
  printf("<p>bind database rebuild (" . posix_getuid() . "/" . posix_geteuid() . ")</p>\n");
  $exit = 0;
  echo "<pre>\n";
  build_dns_config();
  $result = system(DNS_REBUILD, $exit);
  echo "</pre>\n";
  if ($result === FALSE or $exit != 0) {
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
 *   TRUE if password matches.
 */
function check_passwd($host, $pass) {
  global $database;
  // Legacy: passwords are hashed with sha256 before they get stored:
  $pwhash = hash('sha256', $pass);
  
  $dbpass = $database->getPassword($host);
  if (password_verify($pwhash, $dbpass)) {
    return TRUE;
  }
  // Compatibility with the bad old password hashes:
  $compat = password_hash($dbpass, PASSWORD_DEFAULT);
  return password_verify($compat, $pwhash);
}

function update_host($host, $ip) {
  global $database;
  return $database->setAddress($host, $ip);
}

function delete_host($host) {
  global $database;
  return $database->setAddress($host, NULL);
}

$norebuild = FALSE;

if (filter_input(INPUT_POST, 'norebuild')) {
  $norebuild = TRUE;
}

$post_ip = filter_input(INPUT_POST, 'ip');
if (empty($post_ip)) {
  $ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
}
else {
  $ip = filter_var($post_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

if ($ip) {
  $action = filter_input(INPUT_POST, 'action');
  $host = filter_input(INPUT_POST, 'host');
  $pass = filter_input(INPUT_POST, 'pass');
  switch ($action) {
    case "register":
      if (check_passwd($host, $pass) === TRUE) {
        update_host($host, $ip);
        printf("<p>register successful</p>\n");
        if ($norebuild == FALSE) {
          rebuild();
        }
      }
      else {
        printf("<p>register unsuccessful</p>\n");
      }
      break;

    case "unregister":
      if (check_passwd($host, $pass) === TRUE) {
        delete_host($host);
        if ($norebuild == FALSE) {
          rebuild();
        }
      }
      else {
        printf("<p>unregister unsuccessful</p>\n");
      }
      break;

    default:
      printf("<p>ERROR: unknown action</p>");
  }
}
else {
  printf("<p>invalid IP: '" . $ip . "'</p>\n");
}
?>
</body>
</html>
