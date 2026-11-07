#!/bin/sh
wget -q --no-check-certificate --inet4-only \
 --post-data 'host=nindor&pass=geheim&action=register' \
 http://localhost:8080/dynip.php -O -
