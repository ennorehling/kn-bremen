<?php

require_once __DIR__ . '/../database.php';

function usage(string $cmd): void
{
    echo "$cmd Sets the IPv4 or IPv6 address for a host\nUsage: $cmd HOST IP [IP]\n";
}

if ($argc<2) {
    usage($argv[0]);
    exit(1);
}
$ipv6=null;
$ipv4=null;
$host=strtolower($argv[1]);
for ($i=2;$i<$argc;++$i) {
    if ($ip = filter_var($argv[$i], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $ipv4 = $ip;
    }
    elseif ($ip = filter_var($argv[$i], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $ipv6 = $ip;
    }
}
if ($ipv4 or $ipv6) {
    $db = new MySqlDatabase();
    $db->setAddress($host, $ipv4, $ipv6);
}
