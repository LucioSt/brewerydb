#!/bin/bash

echo ""
echo "To verify active instances use: << ps -ef | grep php >> "
echo ""
echo ""
echo ""
echo "To start Memcached server use:  <<  /usr/local/opt/memcached/bin/memcached  >>"
echo ""
echo ""


/Applications/MAMP/bin/php/php7.1.1/bin/php -S localhost:8080 -t web web/index.php
