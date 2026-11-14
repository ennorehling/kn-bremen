<?php

return [
    'rebuild' => env('DNS_REBUILD', '/usr/sbin/rndc reload kn-bremen.de'),
    'file' => env('DNS_FILE', '/etc/bind/kn-bremen.dynip'),
    'db' => env('DNS_DB', '/etc/bind/db.kn-bremen'),
];
