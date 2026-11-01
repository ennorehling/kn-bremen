<?php

require_once __DIR__ . '/../database.php';

function usage(string $cmd): void
{
    echo "$cmd Sets the password for a host (or adds one)\nUsage: $cmd USER PASS\n";
}

if ($argc<3) {
    usage($argv[0]);
    exit(1);
}
$host=strtolower($argv[1]);
$pass=$argv[2];

$db = new MySqlDatabase();
// Legacy: passwords are hashed with sha256 before they get stored:
// $pass = hash('sha256', $pass);
$hash = password_hash($pass, PASSWORD_DEFAULT);
$db->setPassword($host, $hash);
echo $hash . PHP_EOL;