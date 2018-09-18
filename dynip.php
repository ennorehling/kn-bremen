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

/**
 *
 */
function rebuild() {
  printf("<p>bind database rebuild (" . posix_getuid() . "/" . posix_geteuid() . ")</p>\n");
  $exit = 0;
  echo "<pre>\n";
  $result = system(REBUILD_CMD, $exit);
  echo "</pre>\n";
  if ($result === FALSE or $exit != 0) {
    printf("<p>bind database rebuild failed: " . $result . "($exit)</p>\n");
  }
  else {
    printf("<p>bind database rebuilt.</p>\n");
  }
}

/**
 *
 */
function get_passwd($host) {
  $conn = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
  mysqli_select_db($conn, MYSQL_TABLE);
  $query = "select pass from passwd where host='" . mysqli_real_escape_string($conn, $host) . "'";
  $res = mysqli_query($conn, $query);
  if (mysqli_num_rows($res) != 1) {
    mysqli_free_result($res);
    mysqli_close($conn);
    return '_nopass';
  }
  $row = mysqli_fetch_array($res);
  mysqli_free_result($res);
  mysqli_close($conn);
  return $row['pass'];
}

/**
 *
 */
function check_passwd($host, $pass) {
  $crypted_pass = get_passwd($host);
  if ($crypted_pass != "_nopass" && $crypted_pass === hash('sha256', $pass)) {
    return TRUE;
  }
  return FALSE;
}

$norebuild = FALSE;

if (isset($_REQUEST['norebuild'])) {
  $norebuild = TRUE;
}

if (isset($_REQUEST['ip'])) {
  $ip = $_REQUEST['ip'];
}
else {
  $ip = $_SERVER['REMOTE_ADDR'];
}

// if( Net_CheckIP::check_ip($ip)) {.
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
  switch ($_REQUEST['action']) {
    case "register":
      if (check_passwd($_REQUEST['host'], $_REQUEST['pass']) == TRUE) {
        $conn = mysqli_connect('localhost', 'dynip', 'ARa5ohtohwie6foh');
        mysqli_select_db($conn, 'dynip');
        $query = 'replace into mappings values(\'' . $_REQUEST['host'] . '\',\'' . $ip . '\',now())';
        mysqli_query($conn, $query);
        mysqli_close($conn);
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
      if (check_passwd($_REQUEST['host'], $_REQUEST['pass']) == TRUE) {
        $conn = mysqli_connect('localhost', 'dynip', 'ARa5ohtohwie6foh');
        mysqli_select_db($conn, 'dynip');
        $query = "delete from mappings where host='" . $_REQUEST['host'] . "'";
        mysqli_query($conn, $query);
        mysqli_close($conn);
        printf("<p>unregister successful</p>\n");
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
