#!/bin/bash

export QUERY_STRING="action=register&host=enno&pass=enno&ip=90.149.48.145&norebuild=true"

php -e -r 'parse_str($_SERVER["QUERY_STRING"], $_POST); include "dynip.php";'

